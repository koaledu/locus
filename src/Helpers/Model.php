<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Helpers;

abstract class Model
{
    protected static string $table;

    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM " . static::$table . " WHERE id = ?",
            [$id]
        );
    }

    public static function where(string $column, mixed $value): array
    {
        return Database::fetchAll(
            "SELECT * FROM " . static::$table . " WHERE $column = ?",
            [$value]
        );
    }

    public static function whereFirst(string $column, mixed $value): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM " . static::$table . " WHERE $column = ?",
            [$value]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(static::$table, $data);
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        return Database::fetchOne($sql, $params);
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return Database::fetchAll($sql, $params);
    }
}
