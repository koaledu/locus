<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Helpers;

abstract class Model
{
    protected static string $table;
    protected static array $fillable = [];

    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM " . static::$table . " WHERE id = ?",
            [$id]
        );
    }

    public static function all(): array
    {
        return Database::fetchAll("SELECT * FROM " . static::$table);
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

    public static function update(int $id, array $data): int
    {
        return Database::update(static::$table, $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int
    {
        $stmt = Database::query(
            "DELETE FROM " . static::$table . " WHERE id = ?",
            [$id]
        );
        return $stmt->rowCount();
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        return Database::query($sql, $params);
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
