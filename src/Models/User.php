<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Models;

use App\Helpers\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['name', 'email', 'password_hash', 'role', 'group', 'dni'];

    public static function findByEmail(string $email): ?array
    {
        return self::whereFirst('email', $email);
    }

    public static function findByDni(string $dni): ?array
    {
        return self::whereFirst('dni', $dni);
    }

    public static function allByRole(string $role): array
    {
        return self::where('role', $role);
    }

    public static function countByRole(string $role): int
    {
        $result = self::fetchOne('SELECT COUNT(*) as total FROM users WHERE role = ?', [$role]);
        return $result['total'] ?? 0;
    }

    public static function countByRoleAndGroup(string $role, ?string $group): int
    {
        if ($group === null || $group === '') {
            return self::countByRole($role);
        }
        $result = self::fetchOne(
            'SELECT COUNT(*) as total FROM users WHERE role = ? AND `group` = ?',
            [$role, $group]
        );
        return $result['total'] ?? 0;
    }
}
