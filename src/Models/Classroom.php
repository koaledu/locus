<?php

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
