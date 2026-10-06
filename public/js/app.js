// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

// Locus - Frontend Application

const API_BASE = '/api';

function getToken() {
    return localStorage.getItem('token');
}

function getUser() {
    const stored = localStorage.getItem('user');
    return stored ? JSON.parse(stored) : null;
}

function setTokenCookie(token) {
    document.cookie = 'token=' + token + '; path=/; SameSite=Lax';
}

function clearTokenCookie() {
    document.cookie = 'token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
}

async function apiRequest(path, options = {}) {
    const token = getToken();
    const headers = { ...options.headers };

    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }

    if (!headers['Content-Type'] && options.method && options.method !== 'GET') {
        headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(API_BASE + path, { ...options, headers });
    return res;
}

async function closeSession(sessionId) {
    if (!sessionId) return;
    if (!confirm('¿Cerrar este código QR? Los participantes ya no podrán marcar asistencia.')) return;

    try {
        const res = await apiRequest('/sessions/' + sessionId + '/close', { method: 'POST' });
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (_) {
            alert('Respuesta inesperada (status ' + res.status + '):\n' + text.substring(0, 300));
            return;
        }
        if (res.ok) return data;
        alert('Error ' + res.status + ': ' + (data.error || 'desconocido'));
    } catch (e) {
        alert('Error de conexión: ' + e.message);
    }
}

function renderQR(container, text) {
    container.innerHTML = '';
    new QRCode(container, {
        text: text,
        width: 250,
        height: 250,
        colorDark: '#1e293b',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
}

// Auth
document.addEventListener('DOMContentLoaded', () => {
    const user = getUser();
    const navContent = document.getElementById('navContent');
    const logoutBtn = document.getElementById('logoutBtn');

    if (navContent) {
        if (user) {
            navContent.innerHTML = `
                <span class="nav-user">${user.name}</span>
                ${user.role === 'organizer' ? '<a href="/organizer/dashboard" class="nav-link">Panel</a>' : ''}
                ${user.role === 'organizer' ? '<a href="/organizer/locations" class="nav-link">Ubicaciones</a>' : ''}
                ${user.role === 'participant' ? '<a href="/participant/history" class="nav-link">Mi Historial</a>' : ''}
            `;
            if (logoutBtn) logoutBtn.style.display = 'inline-block';
        } else {
            navContent.innerHTML = '<a href="/login" class="nav-link">Iniciar Sesión</a>';
        }
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            const refreshToken = localStorage.getItem('refresh_token');
            if (refreshToken) {
                await apiRequest('/auth/refresh', {
                    method: 'POST',
                    body: JSON.stringify({ refresh_token: refreshToken })
                }).catch(() => {});
            }
            localStorage.removeItem('token');
            localStorage.removeItem('refresh_token');
            localStorage.removeItem('user');
            clearTokenCookie();
            window.location.href = '/login';
        });
    }
});

// Login form
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorEl = document.getElementById('loginError');
        errorEl.textContent = '';

        try {
            const res = await fetch(API_BASE + '/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value
                })
            });
            const data = await res.json();

            if (res.ok) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('refresh_token', data.refresh_token);
                localStorage.setItem('user', JSON.stringify(data.user));
                setTokenCookie(data.token);
                window.location.href = data.user.role === 'organizer' ? '/organizer/dashboard' : '/participant/history';
            } else {
                errorEl.textContent = data.error || 'Error al iniciar sesión';
            }
        } catch (e) {
            errorEl.textContent = 'Error de conexión';
        }
    });
}

// Register form
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorEl = document.getElementById('registerError');
        errorEl.textContent = '';

        try {
            const res = await fetch(API_BASE + '/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    document_id: document.getElementById('document_id').value,
                    password: document.getElementById('password').value,
                    role: document.getElementById('role').value,
                    group: document.getElementById('group') ? document.getElementById('group').value : null
                })
            });
            const data = await res.json();

            if (res.ok) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('refresh_token', data.refresh_token);
                localStorage.setItem('user', JSON.stringify(data.user));
                setTokenCookie(data.token);
                window.location.href = data.user.role === 'organizer' ? '/organizer/dashboard' : '/participant/history';
            } else {
                errorEl.textContent = data.error || 'Error al registrarse';
            }
        } catch (e) {
            errorEl.textContent = 'Error de conexión';
        }
    });
}

// Protected route check
document.addEventListener('DOMContentLoaded', () => {
    const protectedPaths = ['/organizer/dashboard', '/organizer/session/create', '/organizer/reports', '/organizer/locations', '/participant/history'];
    const currentPath = window.location.pathname;

    if (protectedPaths.includes(currentPath) && !getToken()) {
        window.location.href = '/login';
    }
});
