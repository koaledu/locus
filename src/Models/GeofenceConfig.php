<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Models;

use App\Helpers\Model;

class GeofenceConfig extends Model
{
    protected static string $table = 'geofence_config';
    protected static array $fillable = ['classroom_id', 'validation_mode'];

    public static function findByClassroom(int $classroomId): ?array
    {
        return self::whereFirst('classroom_id', $classroomId);
    }
}
