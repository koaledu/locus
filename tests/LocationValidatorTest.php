<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

use App\Services\LocationValidator;
use PHPUnit\Framework\TestCase;

class LocationValidatorTest extends TestCase
{
    public function testGPSWithinRadius(): void
    {
        $valid = LocationValidator::validateGPS(
            7.1000, -73.1000,
            7.1001, -73.1001,
            50
        );
        $this->assertTrue($valid);
    }

    public function testGPSOutsideRadius(): void
    {
        $valid = LocationValidator::validateGPS(
            7.2000, -73.2000,
            7.1000, -73.1000,
            50
        );
        $this->assertFalse($valid);
    }

    public function testGPSExactCenter(): void
    {
        $valid = LocationValidator::validateGPS(
            7.1000, -73.1000,
            7.1000, -73.1000,
            10
        );
        $this->assertTrue($valid);
    }

    public function testNetworkIPInRange(): void
    {
        $valid = LocationValidator::validateNetwork(
            '192.168.1.50',
            ['192.168.1.0/24']
        );
        $this->assertTrue($valid);
    }

    public function testNetworkIPOutsideRange(): void
    {
        $valid = LocationValidator::validateNetwork(
            '10.0.0.1',
            ['192.168.1.0/24']
        );
        $this->assertFalse($valid);
    }

    public function testMultipleRanges(): void
    {
        $valid = LocationValidator::validateNetwork(
            '10.0.0.5',
            ['192.168.1.0/24', '10.0.0.0/8']
        );
        $this->assertTrue($valid);
    }

    public function testExactIPMatch(): void
    {
        $valid = LocationValidator::validateNetwork(
            '200.100.50.25',
            ['200.100.50.25']
        );
        $this->assertTrue($valid);
    }

    public function testEmptyRangesReturnsFalse(): void
    {
        $valid = LocationValidator::validateNetwork('192.168.1.1', []);
        $this->assertFalse($valid);
    }

    public function testSSIDMatch(): void
    {
        $this->assertTrue(
            LocationValidator::validateSSID('UDI-WiFi', 'UDI-WiFi')
        );
    }

    public function testSSIDCaseInsensitiveMatch(): void
    {
        $this->assertTrue(
            LocationValidator::validateSSID('udi-wifi', 'UDI-WiFi')
        );
    }

    public function testSSIDMismatch(): void
    {
        $this->assertFalse(
            LocationValidator::validateSSID('Public-WiFi', 'UDI-WiFi')
        );
    }

    public function testHaversineAccuracy(): void
    {
        // Bogotá to Medellín ~240km, well outside any geocerca
        $valid = LocationValidator::validateGPS(
            4.7110, -74.0721,
            6.2476, -75.5658,
            100
        );
        $this->assertFalse($valid);
    }

    public function testBorderlineRadius(): void
    {
        // ~19 meters away at this latitude (0.00017 deg ~ 19m)
        $valid = LocationValidator::validateGPS(
            7.1000, -73.1000,
            7.1000, -73.10017,
            20
        );
        $this->assertTrue($valid);
    }
}
