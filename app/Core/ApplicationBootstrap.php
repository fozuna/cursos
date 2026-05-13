<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class ApplicationBootstrap
{
    private static bool $initialized = false;

    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        self::ensureDirectories();
        self::runMigrations();

        self::$initialized = true;
    }

    public static function runMigrations(): void
    {
        $pdo = Database::connection();
        self::ensureSchemaMigrationsTable($pdo);

        $migrationFiles = glob(base_path('database/migrations/*.sql'));

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

            if (self::migrationAlreadySatisfied($pdo, $migrationName)) {
                self::markMigrationApplied($pdo, $migrationName);
                continue;
            }

            $sql = file_get_contents($migrationFile);

            if ($sql === false) {
                throw new RuntimeException(sprintf('Nao foi possivel ler a migration: %s', $migrationName));
            }

            $pdo->exec($sql);
            self::markMigrationApplied($pdo, $migrationName);
        }
    }

    private static function ensureDirectories(): void
    {
        ensure_directory(storage_path('logs'));
        ensure_directory((string) config('app.public_storage_path'));
        ensure_directory((string) config('app.upload_path'));
    }

    private static function ensureSchemaMigrationsTable(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                migration VARCHAR(190) NOT NULL PRIMARY KEY,
                executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private static function markMigrationApplied(PDO $pdo, string $migrationName): void
    {
        $statement = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:migration)');
        $statement->execute(['migration' => $migrationName]);
    }

    private static function migrationAlreadySatisfied(PDO $pdo, string $migrationName): bool
    {
        return match ($migrationName) {
            '001_create_certificate_module.sql' => self::tablesExist($pdo, [
                'companies',
                'certificate_templates',
                'students',
                'generation_batches',
                'certificates',
                'generation_histories',
            ]),
            '002_add_program_content_to_certificates.sql' => self::columnExists($pdo, 'certificates', 'program_content'),
            default => false,
        };
    }

    /**
     * @param array<int, string> $tables
     */
    private static function tablesExist(PDO $pdo, array $tables): bool
    {
        foreach ($tables as $table) {
            if (!self::tableExists($pdo, $table)) {
                return false;
            }
        }

        return true;
    }

    private static function tableExists(PDO $pdo, string $table): bool
    {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = :schema AND table_name = :table_name'
        );
        $statement->execute([
            'schema' => (string) config('database.database'),
            'table_name' => $table,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    private static function columnExists(PDO $pdo, string $table, string $column): bool
    {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = :schema AND table_name = :table_name AND column_name = :column_name'
        );
        $statement->execute([
            'schema' => (string) config('database.database'),
            'table_name' => $table,
            'column_name' => $column,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }
}
