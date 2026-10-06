<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

use App\Services\LocationValidator;
use PHPUnit\Framework\TestCase;

class LocationValidatorSubnetTest extends TestCase
{
    public function testDerivesSlash24Subnet(): void
    {
        $this->assertSame('192.168.10.0/24', LocationValidator::subnetOf('192.168.10.47'));
    }

    public function testDerivesSlash16Subnet(): void
    {
        $this->assertSame('10.20.0.0/16', LocationValidator::subnetOf('10.20.31.9', 16));
    }

    public function testNetworkAddressOfExactHostIsItself(): void
    {
        $this->assertSame('192.168.1.5/32', LocationValidator::subnetOf('192.168.1.5', 32));
    }

    public function testLoopbackSubnet(): void
    {
        $this->assertSame('127.0.0.0/8', LocationValidator::subnetOf('127.0.0.1', 8));
    }

    public function testRejectsGarbageInput(): void
    {
        $this->assertNull(LocationValidator::subnetOf(''));
        $this->assertNull(LocationValidator::subnetOf('not-an-ip'));
        $this->assertNull(LocationValidator::subnetOf('999.1.1.1'));
    }

    public function testRejectsInvalidPrefixLengths(): void
    {
        $this->assertNull(LocationValidator::subnetOf('192.168.1.1', 0));
        $this->assertNull(LocationValidator::subnetOf('192.168.1.1', 33));
    }

    public function testDetectsPrivateAddresses(): void
    {
        $this->assertTrue(LocationValidator::isPrivateIp('192.168.10.47'));
        $this->assertTrue(LocationValidator::isPrivateIp('10.0.0.1'));
        $this->assertTrue(LocationValidator::isPrivateIp('172.16.5.9'));
        $this->assertTrue(LocationValidator::isPrivateIp('172.31.255.255'));
        $this->assertTrue(LocationValidator::isPrivateIp('127.0.0.1'));
    }

    public function testDetectsPublicAddresses(): void
    {
        $this->assertFalse(LocationValidator::isPrivateIp('8.8.8.8'));
        $this->assertFalse(LocationValidator::isPrivateIp('2001:db8::1'));
        // 172.15 and 172.32 sit outside the RFC1918 172.16/12 block
        $this->assertFalse(LocationValidator::isPrivateIp('172.15.0.1'));
        $this->assertFalse(LocationValidator::isPrivateIp('172.32.0.1'));
    }

    public function testV6SubnetDefaultsToSlash64(): void
    {
        $this->assertSame('2800:e2:6c80:30a::/64', LocationValidator::subnetOf('2800:e2:6c80:30a::d11'));
    }

    public function testV6LoopbackIsPrivate(): void
    {
        $this->assertTrue(LocationValidator::isPrivateIp('::1'));
        $this->assertTrue(LocationValidator::isPrivateIp('fd00::1'));
        $this->assertTrue(LocationValidator::isPrivateIp('fe80::1'));
        $this->assertFalse(LocationValidator::isPrivateIp('2800:e2:6c80:30a::d11'));
    }

    public function testMatchesIpv6Range(): void
    {
        $this->assertTrue(LocationValidator::validateNetwork('2800:e2:6c80:30a::d11', ['2800:e2:6c80:30a::/64']));
        $this->assertFalse(LocationValidator::validateNetwork('2800:e2:6c80:30b::1', ['2800:e2:6c80:30a::/64']));
    }

    public function testV4RangeNeverMatchesV6Address(): void
    {
        $this->assertFalse(LocationValidator::validateNetwork('2800:e2:6c80:30a::1', ['192.168.10.0/24']));
        $this->assertFalse(LocationValidator::validateNetwork('192.168.10.5', ['2800:e2:6c80:30a::/64']));
    }

    public function testStillMatchesIpv4Ranges(): void
    {
        $this->assertTrue(LocationValidator::validateNetwork('192.168.10.47', ['192.168.10.0/24']));
        $this->assertFalse(LocationValidator::validateNetwork('192.168.11.47', ['192.168.10.0/24']));
        $this->assertTrue(LocationValidator::validateNetwork('10.1.2.3', ['10.1.0.0/16']));
    }

    public function testExactHostRangeWithoutPrefix(): void
    {
        $this->assertTrue(LocationValidator::validateNetwork('192.168.10.5', ['192.168.10.5']));
        $this->assertFalse(LocationValidator::validateNetwork('192.168.10.6', ['192.168.10.5']));
    }

    public function testRejectsOutOfRangePrefixLength(): void
    {
        $this->assertFalse(LocationValidator::validateNetwork('192.168.10.5', ['192.168.10.0/99']));
    }
}