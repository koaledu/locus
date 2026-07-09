<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Services;

class LocationValidator
{
    public static function validateGPS(float $lat, float $lng, float $centerLat, float $centerLng, int $radiusMeters): bool
    {
        $distance = self::haversineDistance($lat, $lng, $centerLat, $centerLng);
        return $distance <= $radiusMeters;
    }

    public static function validateNetwork(string $ip, array $allowedRanges): bool
    {
        if (empty($allowedRanges)) return false;

        foreach ($allowedRanges as $range) {
            if (self::ipInRange($ip, $range)) return true;
        }
        return false;
    }

    private static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private static function ipInRange(string $ip, string $range): bool
    {
        if (str_contains($range, '/')) {
            [$subnet, $bits] = explode('/', $range);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - (int)$bits);
            return ($ip & $mask) === ($subnet & $mask);
        }
        return $ip === $range;
    }

    public static function validateSSID(string $deviceSsid, string $expectedSsid): bool
    {
        return strcasecmp($deviceSsid, $expectedSsid) === 0;
    }
}
