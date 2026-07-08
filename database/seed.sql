-- SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
--
-- SPDX-License-Identifier: MIT

-- Seed data for Locus
-- All passwords: 123456
-- Hash generated with PHP password_hash('123456', PASSWORD_BCRYPT)

SET NAMES utf8mb4;

INSERT INTO users (name, email, password_hash, role, `group`, dni) VALUES
('Angely', 'angely@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'teacher', 'Psicología', '1001'),
('Alzate', 'alzate@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'teacher', 'Ingeniería de Sistemas', '1002'),
('Angel', 'angel@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', 'Ingeniería de Sistemas', '2001'),
('Farfan', 'farfan@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', 'Ingeniería de Sistemas', '2002'),
('Monsalve', 'monsalve@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', 'Ingeniería de Sistemas', '2003'),
('Botero', 'botero@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', 'Ingeniería de Sistemas', '2004'),
('Malo', 'malo@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', 'Psicología', '2005'),
('Valeria', 'valeria@udi.edu.co', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', 'Psicología', '2006');

INSERT INTO classrooms (name, latitude, longitude, radius_meters, ssid, ip_range) VALUES
('Ana Frank', 7.0587899, -73.8626501, 50, 'WBAF-estudiantes', '192.168.10.0/24'),
('Marie Curie', 7.0623784, -73.8580640, 50, 'WBcaMC-estudiantes', '10.10.0.0/24');

INSERT INTO geofence_config (classroom_id, validation_mode) VALUES
(1, 'gps_or_network'),
(2, 'gps_and_network');
