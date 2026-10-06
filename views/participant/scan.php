<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

?>

<div class="scan-page">
    <?php if (isset($error)): ?>
        <div class="error-card">
            <h1>Error</h1>
            <p><?= htmlspecialchars($error) ?></p>
            <a href="/" class="btn btn-primary">Volver al inicio</a>
        </div>
    <?php elseif (isset($session)): ?>
        <div class="session-info-card">
            <h1>Registrar asistencia</h1>
            <p class="session-title"><strong>Sesión:</strong> <?= htmlspecialchars($session['title']) ?></p>
            <p><strong>Expira:</strong> <span id="expiresAt" class="tabular-nums"><?= htmlspecialchars($session['expires_at']) ?></span></p>
            <p><strong>Validación:</strong>
                <?= $session['location_id'] === null ? 'Solo QR' : 'Dentro de la ubicación (GPS)' ?>
            </p>
        </div>

        <div id="authRequired" class="card">
            <p>Debes iniciar sesión para registrar tu asistencia.</p>
            <button onclick="window.location.href='/login'" class="btn btn-primary">Iniciar Sesión</button>
        </div>

        <div id="scanStatus" class="card" style="display:none">
            <div id="statusMessage" class="status-message">
                <div class="spinner"></div>
                <p>Verificando ubicación y registrando asistencia...</p>
            </div>
        </div>

        <div id="scanResult" style="display:none" class="card"></div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');
    const authRequired = document.getElementById('authRequired');
    const scanStatus = document.getElementById('scanStatus');
    const scanResult = document.getElementById('scanResult');

    if (!token) {
        authRequired.style.display = 'block';
        return;
    }

    <?php if (isset($session)): ?>
    authRequired.style.display = 'none';
    scanStatus.style.display = 'block';

    try {
        let latitude = null;
        let longitude = null;
        let gpsError = null;

        try {
            const pos = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            });
            latitude = pos.coords.latitude;
            longitude = pos.coords.longitude;
        } catch (e) {
            gpsError = e.code === 1
                ? 'Permiso de ubicación denegado. Actívalo en los ajustes del navegador.'
                : 'No se pudo obtener tu ubicación. Revisa que el GPS esté encendido y que la señal sea buena.';
        }

        // A geofenced session cannot pass without coordinates, so say why
        // instead of POSTing nulls and surfacing a generic rejection.
        const needsGps = <?= $session['location_id'] === null ? 'false' : 'true' ?>;
        if (needsGps && gpsError) {
            document.getElementById('statusMessage').innerHTML = '';
            scanResult.style.display = 'block';
            scanResult.className = 'card error';
            scanResult.innerHTML = `
                <h2>❌ GPS no disponible</h2>
                <p>${gpsError}</p>
                <p>Esta sesión pide validar la ubicación, así que sin GPS no se puede registrar.</p>
            `;
            return;
        }

        const res = await fetch('/api/attendance/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                session_id: <?= $session['id'] ?>,
                token: '<?= htmlspecialchars($session['qr_token']) ?>',
                latitude: latitude,
                longitude: longitude
            })
        });

        const data = await res.json();

        document.getElementById('statusMessage').innerHTML = '';

        if (res.ok) {
            scanResult.style.display = 'block';
            scanResult.className = 'card success';
            scanResult.innerHTML = `
                <h2>✅ Asistencia registrada</h2>
                <p>${data.message}</p>
                <p><strong>Validado por:</strong> ${data.validated_by === 'gps' ? 'GPS' : 'Solo QR'}</p>
            `;
        } else {
            scanResult.style.display = 'block';
            scanResult.className = 'card error';
            scanResult.innerHTML = `
                <h2>❌ Error</h2>
                <p>${data.error || 'No se pudo registrar la asistencia'}</p>
                <button onclick="location.reload()" class="btn btn-primary">Intentar de nuevo</button>
            `;
        }
    } catch (e) {
        document.getElementById('statusMessage').innerHTML = '';
        scanResult.style.display = 'block';
        scanResult.className = 'card error';
        scanResult.innerHTML = `<h2>❌ Error de conexión</h2><p>Verifica tu conexión e intenta de nuevo.</p>`;
    }

    scanStatus.style.display = 'none';
    <?php endif; ?>
});

const expiresEl = document.getElementById('expiresAt');
if (expiresEl) {
    const expires = new Date(expiresEl.textContent.replace(' ', 'T') + '-05:00');
    const interval = setInterval(() => {
        const now = new Date();
        const diff = expires - now;
        if (diff <= 0) {
            expiresEl.textContent = 'Expirada';
            expiresEl.style.color = 'red';
            clearInterval(interval);
        } else {
            const mins = Math.floor(diff / 60000);
            const secs = Math.floor((diff % 60000) / 1000);
            expiresEl.textContent = `${mins}:${String(secs).padStart(2, '0')}`;
        }
    }, 1000);
}
</script>
