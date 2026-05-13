<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Environment;
use App\Core\ApplicationBootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';
Environment::load(dirname(__DIR__) . '/.env');

$databaseConfig = config('database');
$serverDsn = sprintf(
    '%s:host=%s;port=%d;charset=%s',
    $databaseConfig['driver'],
    $databaseConfig['host'],
    $databaseConfig['port'],
    $databaseConfig['charset']
);

$serverPdo = new PDO(
    $serverDsn,
    $databaseConfig['username'],
    $databaseConfig['password'],
    $databaseConfig['options']
);

$serverPdo->exec(
    sprintf(
        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
        str_replace('`', '``', $databaseConfig['database']),
        $databaseConfig['charset'],
        $databaseConfig['collation']
    )
);

Database::connection();
ApplicationBootstrap::runMigrations();

echo "Migration executada com sucesso.\n";
