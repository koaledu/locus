<div class="create-session">
    <h1>Crear Nueva Sesión</h1>

    <form id="createSessionForm">
        <div class="form-group">
            <label for="title">Título de la sesión</label>
            <input type="text" id="title" name="title" placeholder="Ej: Semillero de Investigación - Sesión 5" required>
        </div>

        <div class="form-group">
            <label for="classroom_id">Sede</label>
            <select id="classroom_id" name="classroom_id">
                <option value="">Sin sede (solo QR)</option>
                <?php if (!empty($classrooms)): ?>
                    <?php foreach ($classrooms as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="expires_at">Hora de cierre</label>
            <input type="time" id="expires_at" name="expires_at">
            <small class="hint">Ej: 5:00 PM — déjalo vacío para usar los minutos de duración</small>
        </div>

        <div class="form-group">
            <label for="expiry_minutes">O duración (minutos)</label>
            <input type="number" id="expiry_minutes" name="expiry_minutes" value="15" min="1" max="300">
        </div>

        <button type="submit" class="btn btn-primary">Generar QR</button>
    </form>

    <div id="qrResult" style="display:none" class="qr-result">
        <h2>Código QR Generado</h2>
        <div class="qr-display" id="qrContainer"></div>
        <div class="qr-info">
            <p><strong>Token:</strong> <code id="qrToken"></code></p>
            <p><strong>Expira:</strong> <span id="qrExpires" class="tabular-nums"></span></p>
            <p><strong>Enlace:</strong> <a id="qrLink" href="" target="_blank"></a></p>
        </div>
        <button id="closeSessionBtn" class="btn btn-danger">Cerrar Sesión</button>
    </div>
</div>

<script>
let currentSessionId = null;

document.getElementById('createSessionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Generando...';

    try {
        const res = await fetch('/api/sessions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            body: JSON.stringify({
                title: document.getElementById('title').value,
                classroom_id: document.getElementById('classroom_id').value || null,
                expires_at: document.getElementById('expires_at').value || null,
                expiry_minutes: parseInt(document.getElementById('expiry_minutes').value) || 15
            })
        });

        const data = await res.json();

        if (res.ok) {
            currentSessionId = data.session.id;
            const qrUrl = window.location.origin + '/api/attendance/scan?token=' + data.session.token + '&session=' + data.session.id;
            document.getElementById('qrToken').textContent = data.session.token;
            document.getElementById('qrExpires').textContent = new Date(data.session.expires_at.replace(' ', 'T') + '-05:00').toLocaleString('es-CO');
            document.getElementById('qrLink').href = qrUrl;
            document.getElementById('qrLink').textContent = qrUrl;
            document.getElementById('qrResult').style.display = 'block';
            document.getElementById('createSessionForm').style.display = 'none';

            const qrContainer = document.getElementById('qrContainer');
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: qrUrl,
                width: 250,
                height: 250,
                colorDark: '#1e293b',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } else {
            alert(data.error || 'Error al crear sesión');
        }
    } catch (e) {
        alert('Error de conexión');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Generar QR';
    }
});

document.getElementById('closeSessionBtn').addEventListener('click', async () => {
    if (!currentSessionId) return;
    if (!confirm('¿Cerrar esta sesión? Los estudiantes ya no podrán marcar asistencia.')) return;

    try {
        const res = await fetch('/api/sessions/' + currentSessionId + '/close', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });

        if (res.ok) {
            alert('Sesión cerrada');
            window.location.href = '/teacher/dashboard';
        }
    } catch (e) {
        alert('Error al cerrar sesión');
    }
});
</script>
