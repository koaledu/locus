// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: MIT

<div class="auth-form">
    <h1>Iniciar Sesión</h1>
    <form id="loginForm">
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Ingresar</button>
    </form>
    <p class="auth-link">¿No tienes cuenta? <a href="/register">Regístrate</a></p>
    <div id="loginError" class="error-msg"></div>
</div>
