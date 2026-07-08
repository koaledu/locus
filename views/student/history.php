// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

<div class="history">
    <h1>Mi Historial de Asistencia</h1>

    <div id="historyList">
        <p class="loading">Cargando historial...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('/api/attendance/history', {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
        });
        const data = await res.json();
        const list = document.getElementById('historyList');

        if (data.attendance && data.attendance.length > 0) {
            list.innerHTML = data.attendance.map(a => `
                <div class="history-item">
                    <div class="history-info">
                        <strong>${a.session_title}</strong>
                        <span class="history-meta">${new Date(a.session_date.replace(' ', 'T') + '-05:00').toLocaleDateString('es-CO')} - ${new Date(a.created_at.replace(' ', 'T') + '-05:00').toLocaleTimeString('es-CO')}</span>
                        <span class="history-classroom">${a.classroom_name || 'Sin sede'}</span>
                    </div>
                    <span class="badge badge-${a.validated_by}">${a.validated_by}</span>
                </div>
            `).join('');
        } else {
            list.innerHTML = '<p class="empty">No tienes registros de asistencia.</p>';
        }
    } catch (e) {
        document.getElementById('historyList').innerHTML = '<p class="error">Error al cargar historial</p>';
    }
});
</script>
