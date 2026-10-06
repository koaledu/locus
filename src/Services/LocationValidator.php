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
        if (!str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range, 2);
        $bits = (int)$bits;
        $addr = @inet_pton($ip);
        $net = @inet_pton($subnet);

        // inet_packed masks by family, so an IPv4 range can never match
        // an IPv6 address; null on garbage, false on family mismatch.
        if ($addr === false || $net === false || strlen($addr) !== strlen($net)) {
            return false;
        }
        if ($bits < 0 || $bits > strlen($addr) * 8) {
            return false;
        }

        return self::maskEquals($addr, $net, $bits);
    }

    private static function maskEquals(string $a, string $b, int $bits): bool
    {
        $bytes = intdiv($bits, 8);
        $spare = $bits % 8;

        if ($bytes > 0 && strncmp($a, $b, $bytes) !== 0) {
            return false;
        }
        if ($spare === 0) {
            return true;
        }

        $mask = chr(0xFF << (8 - $spare) & 0xFF);
        return ($a[$bytes] & $mask) === ($b[$bytes] & $mask);
    }

    public static function validateSSID(string $deviceSsid, string $expectedSsid): bool
    {
        return strcasecmp($deviceSsid, $expectedSsid) === 0;
    }

    // Derives the subnet that contains this IP, e.g. 192.168.10.47 + 24
    // becomes 192.168.10.0/24. Returns null for anything unparseable.
    public static function subnetOf(string $ip, ?int $bits = null): ?string
    {
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return null;
        }

        $isV6 = strlen($packed) === 16;
        $width = strlen($packed) * 8;

        // A v6 address gets a /64, the smallest subnet a single LAN uses.
        $bits ??= $isV6 ? 64 : 24;
        if ($bits < 1 || $bits > $width) {
            return null;
        }

        $bytes = intdiv($bits, 8);
        $spare = $bits % 8;
        $network = substr($packed, 0, $bytes);

        if ($spare !== 0) {
            $network .= chr(ord($packed[$bytes]) & (0xFF << (8 - $spare) & 0xFF));
        }

        // Zero the host bits; inet_ntop needs a full 4 or 16 byte address.
        $network .= str_repeat("\0", strlen($packed) - strlen($network));

        $text = inet_ntop($network);
        return $text === false ? null : "$text/$bits";
    }

    // Loopback and private ranges only: a public address here means the
    // request came through a tunnel or NAT, not from the real LAN.
    public static function isPrivateIp(string $ip): bool
    {
        if (str_contains($ip, ':')) {
            return (bool) preg_match('/^(::1$|f[cd]|fe[89ab])/i', $ip);
        }
        return (bool) preg_match(
            '/^(127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/',
            $ip
        );
    }
}
