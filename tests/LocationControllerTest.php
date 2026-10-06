<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

use App\Controllers\LocationController;
use PHPUnit\Framework\TestCase;

class LocationControllerTest extends TestCase
{
    private function valid(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Ana Frank',
            'latitude' => '4.6012345',
            'longitude' => '-74.0654321',
            'radius_meters' => '50',
        ];
    }

    public function testAcceptsValidPayload(): void
    {
        $this->assertNull(LocationController::validate($this->valid()));
    }

    public function testAcceptsMissingOptionalFields(): void
    {
        $data = $this->valid();
        unset($data['radius_meters']);

        $this->assertNull(LocationController::validate($data));
    }

    public function testRejectsEmptyName(): void
    {
        $this->assertSame('Nombre requerido', LocationController::validate($this->valid(['name' => '   '])));
    }

    public function testRejectsOutOfRangeLatitude(): void
    {
        $this->assertSame(
            'Coordenada inválida: latitude',
            LocationController::validate($this->valid(['latitude' => '91']))
        );
    }

    public function testRejectsOutOfRangeLongitude(): void
    {
        $this->assertSame(
            'Coordenada inválida: longitude',
            LocationController::validate($this->valid(['longitude' => '-181']))
        );
    }

    public function testRejectsMissingCoordinates(): void
    {
        $data = $this->valid();
        unset($data['latitude']);

        $this->assertSame('Coordenada inválida: latitude', LocationController::validate($data));
    }

    public function testRejectsNonNumericCoordinates(): void
    {
        $this->assertSame(
            'Coordenada inválida: longitude',
            LocationController::validate($this->valid(['longitude' => 'aqui']))
        );
    }

    public function testRejectsZeroRadius(): void
    {
        $this->assertSame(
            'Radio inválido',
            LocationController::validate($this->valid(['radius_meters' => '0']))
        );
    }
}