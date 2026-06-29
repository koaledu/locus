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
            <label for="dni">DNI / Documento</label>
            <input type="text" id="dni" name="dni">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
            <label for="role">Rol</label>
            <select id="role" name="role" required>
                <option value="student">Estudiante</option>
                <option value="teacher">Docente</option>
            </select>
        </div>
        <div class="form-group" id="groupField" style="display:none">
            <label for="group">Semillero de investigación</label>
            <select id="group" name="group">
                <option value="">Sin semillero</option>
                <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                <option value="Psicología">Psicología</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Crear cuenta</button>
    </form>
    <p class="auth-link">¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>
    <div id="registerError" class="error-msg"></div>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    document.getElementById('groupField').style.display = this.value === 'student' ? 'block' : 'none';
});
</script>
