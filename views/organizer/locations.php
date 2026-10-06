<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

$center = $locations[0] ?? null;
$centerLat = $center ? (float)$center['latitude'] : 4.6;
$centerLng = $center ? (float)$center['longitude'] : -74.0;
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="create-session">
    <h1>Ubicaciones de registro</h1>
    <p class="subtitle">Haz clic en el mapa para fijar la ubicación</p>

    <form id="locationForm">
        <div class="map" id="map"></div>

        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" placeholder="Ej: Ana Frank" required>
        </div>

        <div class="form-group">
            <label for="latitude">Latitud</label>
            <input type="text" id="latitude" name="latitude" inputmode="decimal" required>
        </div>

        <div class="form-group">
            <label for="longitude">Longitud</label>
            <input type="text" id="longitude" name="longitude" inputmode="decimal" required>
        </div>

        <div class="form-group">
            <label for="radius_meters">Radio (metros)</label>
            <div class="slider-row">
                <input type="range" id="radius_meters" name="radius_meters" min="5" max="500" step="5" value="50">
                <output id="radiusOut" for="radius_meters" class="tabular-nums">50 m</output>
            </div>
        </div>


        <button type="submit" class="btn btn-primary">Guardar ubicación</button>
        <span id="locationError" class="error-msg"></span>
    </form>

    <h2>Ubicaciones registradas</h2>
    <?php if (empty($locations)): ?>
        <p class="empty">No hay ubicaciones registradas.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Coordenadas</th>
                    <th>Radio</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($locations as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td class="tabular-nums"><?= htmlspecialchars($c['latitude'] . ', ' . $c['longitude']) ?></td>
                    <td><?= (int)$c['radius_meters'] ?> m</td>


                    <td><button type="button" class="btn btn-danger btn-sm delete-location" data-id="<?= (int)$c['id'] ?>">Eliminar</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
const token = localStorage.getItem('token');
const authHeaders = { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token };

const map = L.map('map').setView([<?= $centerLat ?>, <?= $centerLng ?>], 18);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

let circle = null;

function placePin(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(7);
    document.getElementById('longitude').value = lng.toFixed(7);

    const radius = parseInt(document.getElementById('radius_meters').value) || 50;
    if (circle) map.removeLayer(circle);
    circle = L.circle([lat, lng], {
        radius: radius,
        color: '#1e66f5',
        fillColor: '#1e66f5',
        fillOpacity: 0.15
    }).addTo(map);
}

map.on('click', e => placePin(e.latlng.lat, e.latlng.lng));


document.getElementById('radius_meters').addEventListener('input', () => {
    const meters = parseInt(document.getElementById('radius_meters').value);
    document.getElementById('radiusOut').textContent = meters + ' m';

    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    if (!isNaN(lat) && !isNaN(lng)) placePin(lat, lng);
});

document.getElementById('locationForm').addEventListener('submit', async e => {
    e.preventDefault();
    const errorEl = document.getElementById('locationError');
    errorEl.textContent = '';

    const body = {
        name: document.getElementById('name').value,
        latitude: document.getElementById('latitude').value,
        longitude: document.getElementById('longitude').value,
        radius_meters: document.getElementById('radius_meters').value
    };

    try {
        const res = await fetch('/api/locations', { method: 'POST', headers: authHeaders, body: JSON.stringify(body) });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            errorEl.textContent = data.error || 'Error al guardar la ubicación';
            return;
        }
        window.location.reload();
    } catch (e) {
        errorEl.textContent = 'Error de conexión';
    }
});

document.querySelectorAll('.delete-location').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('¿Eliminar esta ubicación? Las sesiones que la usen quedarán sin ubicación.')) return;
        const res = await fetch('/api/locations/' + btn.dataset.id, { method: 'DELETE', headers: authHeaders });
        if (res.ok) window.location.reload();
        else alert('Error al eliminar');
    });
});
</script>