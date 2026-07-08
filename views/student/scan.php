// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

<div class="scan-page">
    <?php if (isset($error)): ?>
        <div class="error-card">
            <h1>Error</h1>
            <p><?= htmlspecialchars($error) ?></p>
            <a href="/" class="btn btn-primary">Volver al inicio</a>
        </div>
    <?php elseif (isset($session)): ?>
        <div class="session-info-card">
            <h1>Registrar Asistencia</h1>
            <p class="session-title"><strong>Sesión:</strong> <?= htmlspecialchars($session['title']) ?></p>
            <p><strong>Expira:</strong> <span id="expiresAt" class="tabular-nums"><?= htmlspecialchars($session['expires_at']) ?></span></p>
            <p><strong>Validación:</strong>
                <?php
                $modeLabels = [
                    'gps_only' => 'Solo GPS',
                    'network_only' => 'Solo Red',
                    'gps_or_network' => 'GPS o Red',
                    'gps_and_network' => 'GPS y Red',
                    'none' => 'Sin validación',
                ];
                echo $modeLabels[$validation_mode] ?? 'GPS o Red';
                ?>
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
            console.log('GPS no disponible, se intentará solo validación por red');
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
                longitude: longitude,
                ssid: ''  // Could be populated via additional JS
            })
        });

        const data = await res.json();

        document.getElementById('statusMessage').innerHTML = '';

        if (res.ok) {
            scanResult.style.display = 'block';
            scanResult.className = 'card success';
            scanResult.innerHTML = `
                <h2>✅ Asistencia Registrada</h2>
                <p>${data.message}</p>
                <p><strong>Validado por:</strong> ${data.validated_by === 'gps' ? 'GPS' : data.validated_by === 'network' ? 'Red' : data.validated_by === 'none' ? 'Sin validación' : 'GPS + Red'}</p>
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
