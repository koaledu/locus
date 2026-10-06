<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

?>

<div class="auth-form">
    <h1>Registrarse</h1>
    <form id="registerForm">
        <div class="form-group">
            <label for="name">Nombre completo</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="document_id">Documento de identidad</label>
            <input type="text" id="document_id" name="document_id">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
            <label for="role">Rol</label>
            <select id="role" name="role" required>
                <option value="participant">Participante</option>
                <option value="organizer">Organizador</option>
            </select>
        </div>
        <div class="form-group">
            <label for="group">Grupo o equipo (opcional)</label>
            <input type="text" id="group" name="group" placeholder="Ej: Equipo de logística">
        </div>
        <button type="submit" class="btn btn-primary">Crear cuenta</button>
    </form>
    <p class="auth-link">¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>
    <div id="registerError" class="error-msg"></div>
</div>
