<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
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
