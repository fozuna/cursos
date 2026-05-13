<?php

declare(strict_types=1);

namespace App\Repositories;

final class BatchRepository extends AbstractRepository
{
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO generation_batches (company_id, name, import_source, source_file, total_items, processed_items, status, started_at)
             VALUES (:company_id, :name, :import_source, :source_file, :total_items, 0, :status, NOW())',
            [
                'company_id' => $data['company_id'],
                'name' => $data['name'],
                'import_source' => $data['import_source'],
                'source_file' => $data['source_file'] ?? null,
                'total_items' => $data['total_items'],
                'status' => $data['status'] ?? 'processing',
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateProgress(int $batchId, int $processedItems, string $status = 'processing'): void
    {
        $this->execute(
            'UPDATE generation_batches
             SET processed_items = :processed_items,
                 status = :status,
                 finished_at = CASE WHEN :is_completed = 1 THEN NOW() ELSE NULL END
             WHERE id = :id',
            [
                'processed_items' => $processedItems,
                'status' => $status,
                'is_completed' => $status === 'completed' ? 1 : 0,
                'id' => $batchId,
            ]
        );
    }

    public function latestByCompany(int $companyId, int $limit = 10): array
    {
        return $this->execute(
            'SELECT * FROM generation_batches WHERE company_id = :company_id ORDER BY created_at DESC LIMIT ' . (int) $limit,
            ['company_id' => $companyId]
        )->fetchAll();
    }
}
