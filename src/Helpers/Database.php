<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Helpers;

class Database
{
    private static ?\PDO $instance = null;

    public static function getConnection(): \PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            self::$instance = new \PDO($dsn, $config['username'], $config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$instance->exec('SET NAMES utf8mb4');
            self::$instance->exec("SET time_zone = '-05:00'");
            self::runMigrations();
        }

        return self::$instance;
    }

    private static function runMigrations(): void
    {
        $migrations = [
            "ALTER TABLE users ADD COLUMN `group` VARCHAR(100) AFTER `role`",
            "ALTER TABLE sessions ADD COLUMN `group` VARCHAR(100) AFTER `classroom_id`",
        ];

        foreach ($migrations as $sql) {
            try {
                self::$instance->exec($sql);
            } catch (\PDOException $e) {
                // Column already exists — ignore
            }
        }
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params);
        $row = $result->fetch();
        return $row ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        $result = self::query($sql, $params);
        return $result->fetchAll();
    }

    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_map(fn($col) => "`$col`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        self::query($sql, array_values($data));

        return (int)self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = implode(', ', array_map(fn($col) => "`$col` = ?", array_keys($data)));
        $sql = "UPDATE $table SET $sets WHERE $where";

        $stmt = self::query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }
}
