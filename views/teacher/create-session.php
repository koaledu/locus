<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

?>

<div class="create-session">
    <h1>Crear nueva sesión</h1>

    <form id="createSessionForm">
        <div class="form-group">
            <label for="title">Título de la sesión</label>
            <input type="text" id="title" name="title" placeholder="Ej: Charla inaugural - Sesión 1" required>
        </div>

        <div class="form-group">
            <label for="location_id">Ubicación</label>
            <select id="location_id" name="location_id">
                <option value="">Sin ubicación (solo QR)</option>
                <?php if (!empty($locations)): ?>
                    <?php foreach ($locations as $c): ?>
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
        <h2>Código QR generado</h2>
        <div class="qr-display" id="qrContainer"></div>
        <div class="qr-info">
            <p><strong>Token:</strong> <code id="qrToken"></code></p>
            <p><strong>Expira:</strong> <span id="qrExpires" class="tabular-nums"></span></p>
            <p><strong>Enlace:</strong> <a id="qrLink" href="" target="_blank"></a></p>
        </div>
        <button id="closeSessionBtn" class="btn btn-danger">Cerrar QR</button>
        <button id="backToFormBtn" class="btn btn-secondary" style="margin-top:8px">Volver a la lista</button>
    </div>
</div>

<script>
let currentSessionId = null;

document.getElementById('backToFormBtn').addEventListener('click', () => {
    document.getElementById('qrResult').style.display = 'none';
    document.getElementById('createSessionForm').style.display = 'block';
    document.getElementById('closeSessionBtn').style.display = 'inline-block';
});

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
                location_id: document.getElementById('location_id').value || null,
                expires_at: document.getElementById('expires_at').value || null,
                expiry_minutes: parseInt(document.getElementById('expiry_minutes').value) || 15
            })
        });

        const data = await res.json();

        if (res.ok) {
            currentSessionId = data.session.id;
            const qrUrl = data.session.qr_data;
            document.getElementById('qrToken').textContent = data.session.token;
            document.getElementById('qrExpires').textContent = new Date(data.session.expires_at.replace(' ', 'T') + '-05:00').toLocaleString('es-CO');
            document.getElementById('qrLink').href = qrUrl;
            document.getElementById('qrLink').textContent = qrUrl;
            document.getElementById('qrResult').style.display = 'block';
            document.getElementById('createSessionForm').style.display = 'none';

            const qrContainer = document.getElementById('qrContainer');
            renderQR(qrContainer, qrUrl);
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
    const data = await closeSession(currentSessionId);
    if (data) window.location.href = '/teacher/dashboard';
});
</script>
