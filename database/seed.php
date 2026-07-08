<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE {$config['database']}");

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);

    echo "Base de datos creada exitosamente.\n";

    $passwordHash = password_hash('123456', PASSWORD_BCRYPT);

    $pdo->exec("
        INSERT INTO users (name, email, password_hash, role, `group`, dni) VALUES
        ('Angely', 'angely@udi.edu.co', '$passwordHash', 'teacher', 'Psicología', '1001'),
        ('Alzate', 'alzate@udi.edu.co', '$passwordHash', 'teacher', 'Ingeniería de Sistemas', '1002'),
        ('Angel', 'angel@udi.edu.co', '$passwordHash', 'student', 'Ingeniería de Sistemas', '2001'),
        ('Farfan', 'farfan@udi.edu.co', '$passwordHash', 'student', 'Ingeniería de Sistemas', '2002'),
        ('Monsalve', 'monsalve@udi.edu.co', '$passwordHash', 'student', 'Ingeniería de Sistemas', '2003'),
        ('Botero', 'botero@udi.edu.co', '$passwordHash', 'student', 'Ingeniería de Sistemas', '2004'),
        ('Malo', 'malo@udi.edu.co', '$passwordHash', 'student', 'Psicología', '2005'),
        ('Valeria', 'valeria@udi.edu.co', '$passwordHash', 'student', 'Psicología', '2006')
    ");

    $pdo->exec("
        INSERT INTO classrooms (name, latitude, longitude, radius_meters, ssid, ip_range) VALUES
('Ana Frank', 7.0587899, -73.8626501, 50, 'WBAF-estudiantes', '192.168.10.0/24'),
('Marie Curie', 7.0623784, -73.8580640, 50, 'WBcaMC-estudiantes', '10.10.0.0/24')
    ");

    $pdo->exec("
        INSERT INTO geofence_config (classroom_id, validation_mode) VALUES
        (1, 'gps_or_network'),
        (2, 'gps_and_network')
    ");

    echo "Datos de prueba insertados.\n";
    echo "Usuarios de prueba:\n";
    echo "  Docente: juan@docente.com / 123456\n";
    echo "  Estudiante: carlos@estudiante.com / 123456\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
