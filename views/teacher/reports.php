// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

<div class="reports">
    <h1>Reportes de Asistencia</h1>
    <p class="subtitle">Selecciona una sesión para ver el detalle</p>

    <div id="sessionsReportList" class="sessions-list">
        <p class="loading">Cargando QRs...</p>
    </div>

    <div id="sessionDetail" style="display:none" class="session-detail">
        <h2 id="detailTitle"></h2>
        <p id="detailGroup" class="subtitle"></p>
        <div class="detail-stats">
            <div class="stat-card">
                <span class="stat-number" id="totalPresent">0</span>
                <span class="stat-label">Presentes</span>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="totalStudents">0</span>
                <span class="stat-label">Total Estudiantes</span>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="attendancePercent">0%</span>
                <span class="stat-label">Asistencia</span>
            </div>
        </div>

        <a id="exportCsv" href="" class="btn btn-secondary">Exportar CSV</a>

        <table id="attendanceTable" class="table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>DNI</th>
                    <th>Estudiante</th>
                    <th>Hora</th>
                    <th>Validación</th>
                </tr>
            </thead>
            <tbody id="attendanceBody"></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');

    async function loadSessions() {
        const res = await fetch('/api/reports', {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        const data = await res.json();
        const list = document.getElementById('sessionsReportList');

        if (data.sessions && data.sessions.length > 0) {
            list.innerHTML = data.sessions.map(s => `
                <div class="session-item clickable" data-id="${s.id}">
                    <div class="session-info">
                        <strong>${s.title}</strong>
                        <span class="session-meta">${new Date(s.created_at).toLocaleDateString('es-CO')}${s.group ? ' · ' + s.group : ''}</span>
                        <span class="session-count">${s.total_present}/${s.total_students} asistencias</span>
                    </div>
                </div>
            `).join('');

            document.querySelectorAll('.session-item.clickable').forEach(el => {
                el.addEventListener('click', () => loadDetail(el.dataset.id));
            });
        } else {
            list.innerHTML = '<p class="empty">No hay sesiones registradas.</p>';
        }
    }

    async function loadDetail(sessionId) {
        const res = await fetch('/api/reports?session_id=' + sessionId, {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        const data = await res.json();

        document.getElementById('detailTitle').textContent = data.session.title;
        document.getElementById('detailGroup').textContent = data.session.group ? 'Semillero: ' + data.session.group : '';
        document.getElementById('totalPresent').textContent = data.total_present;
        document.getElementById('totalStudents').textContent = data.total_students;
        document.getElementById('attendancePercent').textContent =
            data.total_students > 0 ? Math.round((data.total_present / data.total_students) * 100) + '%' : '0%';
        document.getElementById('exportCsv').href = '/api/reports/' + sessionId + '/csv';

        const tbody = document.getElementById('attendanceBody');
        if (data.students && data.students.length > 0) {
            tbody.innerHTML = data.students.map(s => {
                const attended = s.attendance_id !== null;
                const time = attended ? new Date(s.attended_at).toLocaleTimeString('es-CO') : '-';
                const badge = attended
                    ? '<span class="badge badge-present">✓ Presente</span>'
                    : '<span class="badge badge-absent">✗ Ausente</span>';
                const validation = attended ? (s.validated_by || '-') : '-';
                return `
                    <tr class="${attended ? 'row-present' : 'row-absent'}">
                        <td>${badge}</td>
                        <td>${s.dni || '-'}</td>
                        <td>${s.name}</td>
                        <td>${time}</td>
                        <td>${validation}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5">No hay estudiantes registrados</td></tr>';
        }

        document.getElementById('sessionDetail').style.display = 'block';
        document.getElementById('sessionDetail').scrollIntoView({ behavior: 'smooth' });
    }

    loadSessions();
});
</script>
