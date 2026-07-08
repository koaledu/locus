<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Models;

use App\Helpers\Model;

class Classroom extends Model
{
    protected static string $table = 'classrooms';
    protected static array $fillable = ['name', 'latitude', 'longitude', 'radius_meters', 'ssid', 'ip_range'];

    public static function allOrdered(): array
    {
        return self::fetchAll('SELECT * FROM classrooms ORDER BY name');
    }
}
