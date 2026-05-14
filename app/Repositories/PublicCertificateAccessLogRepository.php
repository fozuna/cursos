<?php

declare(strict_types=1);

namespace App\Repositories;

class PublicCertificateAccessLogRepository extends AbstractRepository
{
    public function create(array $data): void
    {
        $this->execute(
            'INSERT INTO public_certificate_access_logs (
                company_id, student_id, certificate_id, access_token_id, ip_address, user_agent, action, status, message
             ) VALUES (
                :company_id, :student_id, :certificate_id, :access_token_id, :ip_address, :user_agent, :action, :status, :message
             )',
            [
                'company_id' => $data['company_id'] ?? null,
                'student_id' => $data['student_id'] ?? null,
                'certificate_id' => $data['certificate_id'] ?? null,
                'access_token_id' => $data['access_token_id'] ?? null,
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'] ?? null,
                'action' => $data['action'],
                'status' => $data['status'],
                'message' => $data['message'],
            ]
        );
    }
}
