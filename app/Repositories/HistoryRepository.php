<?php

declare(strict_types=1);

namespace App\Repositories;

final class HistoryRepository extends AbstractRepository
{
    public function create(array $data): void
    {
        $this->execute(
            'INSERT INTO generation_histories (company_id, batch_id, certificate_id, action, status, message, payload_json)
             VALUES (:company_id, :batch_id, :certificate_id, :action, :status, :message, :payload_json)',
            [
                'company_id' => $data['company_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'certificate_id' => $data['certificate_id'] ?? null,
                'action' => $data['action'],
                'status' => $data['status'],
                'message' => $data['message'],
                'payload_json' => $data['payload_json'] ?? null,
            ]
        );
    }

    public function latestByCompany(int $companyId, int $limit = 15): array
    {
        return $this->execute(
            'SELECT * FROM generation_histories
             WHERE company_id = :company_id
             ORDER BY created_at DESC
             LIMIT ' . (int) $limit,
            ['company_id' => $companyId]
        )->fetchAll();
    }
}
