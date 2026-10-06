<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
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

    public function testHaversineAccuracy(): void
    {
        // ~240 km apart, well outside any radius
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
