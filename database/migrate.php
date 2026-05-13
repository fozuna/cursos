<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Environment;

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

$pdo = Database::connection();
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        migration VARCHAR(190) NOT NULL PRIMARY KEY,
        executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$migrationFiles = glob(__DIR__ . '/migrations/*.sql');

if ($migrationFiles === false || $migrationFiles === []) {
    throw new RuntimeException('Nenhuma migration SQL foi encontrada.');
}

sort($migrationFiles, SORT_NATURAL);

$appliedMigrations = $pdo
    ->query('SELECT migration FROM schema_migrations')
    ->fetchAll(PDO::FETCH_COLUMN);

$appliedLookup = array_fill_keys($appliedMigrations, true);

foreach ($migrationFiles as $migrationFile) {
    $migrationName = basename($migrationFile);

    if (isset($appliedLookup[$migrationName])) {
        continue;
    }

    $sql = file_get_contents($migrationFile);

    if ($sql === false) {
        throw new RuntimeException(sprintf('Nao foi possivel ler a migration: %s', $migrationName));
    }

    $pdo->exec($sql);
    $statement = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:migration)');
    $statement->execute(['migration' => $migrationName]);
}

echo "Migration executada com sucesso.\n";
