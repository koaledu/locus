<div class="dashboard">
    <div class="dashboard-header">
        <h1>Panel del Docente</h1>
        <p>Bienvenido, <?= htmlspecialchars($user['name']) ?></p>
    </div>

    <div class="dashboard-actions">
        <a href="/teacher/session/create" class="card">
            <span class="card-icon">+</span>
            <span class="card-text">Nueva Sesión</span>
        </a>
        <a href="/teacher/reports" class="card">
            <span class="card-icon">📊</span>
            <span class="card-text">Reportes</span>
        </a>
    </div>

    <div class="section" id="sessionsSection">
        <h2>Sesiones Recientes</h2>
        <div id="sessionsList" class="sessions-list">
            <p class="loading">Cargando sesiones...</p>
        </div>
    </div>

    <div id="qrResult" style="display:none" class="qr-result">
        <h2 id="qrTitle"></h2>
        <div class="qr-display" id="qrContainer"></div>
        <div class="qr-info">
            <p><strong>Token:</strong> <code id="qrToken"></code></p>
            <p><strong>Expira:</strong> <span id="qrExpires" class="tabular-nums"></span></p>
            <p><strong>Enlace:</strong> <a id="qrLink" href="" target="_blank"></a></p>
        </div>
        <button id="closeSessionBtn" class="btn btn-danger">Cerrar QR</button>
        <button id="backToListBtn" class="btn btn-secondary" style="margin-top:8px">Volver a la lista</button>
    </div>
</div>

<script>
let currentSessionId = null;

document.addEventListener('DOMContentLoaded', async () => {
    const sessionsSection = document.getElementById('sessionsSection');
    const sessionsList = document.getElementById('sessionsList');
    const qrResult = document.getElementById('qrResult');
    const qrContainer = document.getElementById('qrContainer');

    function showQR(session) {
        currentSessionId = session.id;
        document.getElementById('qrTitle').textContent = 'QR: ' + session.title;
        document.getElementById('qrToken').textContent = session.qr_token;
        document.getElementById('qrExpires').textContent = new Date(session.expires_at.replace(' ', 'T') + '-05:00').toLocaleString('es-CO');
        const qrUrl = window.location.origin + '/api/attendance/scan?token=' + session.qr_token + '&session=' + session.id;
        document.getElementById('qrLink').href = qrUrl;
        document.getElementById('qrLink').textContent = qrUrl;
        qrContainer.innerHTML = '';
        new QRCode(qrContainer, {
            text: qrUrl,
            width: 250,
            height: 250,
            colorDark: '#1e293b',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        sessionsSection.style.display = 'none';
        qrResult.style.display = 'block';
    }

    document.getElementById('backToListBtn').addEventListener('click', () => {
        qrResult.style.display = 'none';
        sessionsSection.style.display = 'block';
    });

    document.getElementById('closeSessionBtn').addEventListener('click', async () => {
        if (!currentSessionId) return;
        if (!confirm('¿Cerrar este código QR? Los estudiantes ya no podrán marcar asistencia.')) return;
        try {
            const res = await fetch('/api/sessions/' + currentSessionId + '/close', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
            });
            if (res.ok) {
                alert('Sesión cerrada');
                location.reload();
            }
        } catch (e) {
            alert('Error al cerrar sesión');
        }
    });

    try {
        const res = await fetch('/api/sessions', {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
        });
        const data = await res.json();

        if (data.sessions && data.sessions.length > 0) {
            sessionsList.innerHTML = data.sessions.map(s => `
                <div class="session-item clickable ${s.is_active && s.expires_at > new Date().toISOString() ? 'active' : ''}" data-id="${s.id}">
                    <div class="session-info">
                        <strong>${s.title}</strong>
                        <span class="session-meta">${s.classroom_name || 'Sin sede'} | ${new Date(s.created_at.replace(' ', 'T') + '-05:00').toLocaleString('es-CO')}</span>
                        <span class="session-count">${s.total_present || 0}/${s.total_students || 0} asistencias</span>
                    </div>
                    <span class="session-status ${s.is_active ? 'status-active' : 'status-closed'}">
                        ${s.is_active ? 'Activa' : 'Cerrada'}
                    </span>
                </div>
            `).join('');

            sessionsList.querySelectorAll('.session-item').forEach(el => {
                el.addEventListener('click', async () => {
                    const id = el.dataset.id;
                    try {
                        const r = await fetch('/api/sessions/' + id, {
                            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
                        });
                        const d = await r.json();
                        if (r.ok) showQR(d.session);
                    } catch (e) {
                        alert('Error al cargar sesión');
                    }
                });
            });
        } else {
            sessionsList.innerHTML = '<p class="empty">No hay sesiones aún. Crea una nueva sesión para comenzar.</p>';
        }
    } catch (e) {
        sessionsList.innerHTML = '<p class="error">Error al cargar sesiones</p>';
    }
});
</script>
