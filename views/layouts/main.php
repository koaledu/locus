<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Locus') ?> - Locus</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/" class="nav-brand">Locus</a>
            <div class="nav-links" id="navLinks">
                <span id="navContent"></span>
                <button id="logoutBtn" class="btn btn-sm" style="display:none">Cerrar Sesión</button>
            </div>
        </div>
    </nav>

    <main class="container">
        <?php require $viewPath; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>Locus - Sistema de Gestión de Asistencias &copy; 2026</p>
        </div>
    </footer>

    <script src="/js/app.js"></script>
    <script src="/js/qrcode.min.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
