-- SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
-- SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
-- SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
--
-- SPDX-License-Identifier: Apache-2.0

-- Seed data for Locus
-- All passwords: 123456
-- Hash generated with PHP password_hash('123456', PASSWORD_BCRYPT)

SET NAMES utf8mb4;

INSERT INTO users (name, email, password_hash, role, `group`, dni) VALUES
('Yulmaris Corrales', 'y.corrales@locus-demo.test', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'teacher', NULL, NULL),
('Wilmer Sanclemente', 'w.sanclemente@locus-demo.test', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', NULL, NULL),
('Karla Peñarreta', 'k.penarreta@locus-demo.test', '$2y$10$usIsxvh5c/ZMYNcB4Bqm4ePk8vbe1.85Xb4i8cZfhLn1vjuOtwoGG', 'student', NULL, NULL);

INSERT INTO locations (name, latitude, longitude, radius_meters, ssid, ip_range) VALUES
('Ana Frank', 7.0587899, -73.8626501, 50, 'WBAF-estudiantes', '192.168.10.0/24'),
('Marie Curie', 7.0623784, -73.8580640, 50, 'WBcaMC-estudiantes', '10.10.0.0/24');

INSERT INTO geofence_config (location_id, validation_mode) VALUES
(1, 'gps_or_network'),
(2, 'gps_and_network');
