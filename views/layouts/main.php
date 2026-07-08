// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: MIT

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Locus') ?> - Locus</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
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
