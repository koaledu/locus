<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

return [
    'name' => 'Locus',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => (bool)(getenv('APP_DEBUG') ?: false),
    'url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'jwt_secret' => getenv('JWT_SECRET') ?: 'change-this-secret-key-in-production',
    'jwt_expiry' => 3600,
    'qr_expiry_minutes' => 15,
    'timezone' => 'America/Bogota',
    'default_radius_meters' => 50,
];
