<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = config('database');
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Falha ao conectar com o banco de dados "%s" em %s:%d. Detalhe: %s',
                    $config['database'],
                    $config['host'],
                    $config['port'],
                    $exception->getMessage()
                ),
                0,
                $exception
            );
        }

        return self::$connection;
    }
}
