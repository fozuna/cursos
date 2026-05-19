<?php

declare(strict_types=1);

namespace App\Repositories;

class CertificateIssueRegistryRepository extends AbstractRepository
{
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO certificate_issue_registry (
                company_id, batch_id, certificate_id, student_id, issue_identifier, certificate_code,
                full_name_snapshot, course_name, completion_date, workload_hours, requested_by,
                requested_ip, emission_status, blocked_reason, payload_json, issued_at
             ) VALUES (
                :company_id, :batch_id, :certificate_id, :student_id, :issue_identifier, :certificate_code,
                :full_name_snapshot, :course_name, :completion_date, :workload_hours, :requested_by,
                :requested_ip, :emission_status, :blocked_reason, :payload_json, :issued_at
             )',
            [
                'company_id' => $data['company_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'certificate_id' => $data['certificate_id'] ?? null,
                'student_id' => $data['student_id'] ?? null,
                'issue_identifier' => $data['issue_identifier'],
                'certificate_code' => $data['certificate_code'] ?? null,
                'full_name_snapshot' => $data['full_name_snapshot'],
                'course_name' => $data['course_name'],
                'completion_date' => $data['completion_date'],
                'workload_hours' => $data['workload_hours'],
                'requested_by' => $data['requested_by'],
                'requested_ip' => $data['requested_ip'] ?? null,
                'emission_status' => $data['emission_status'],
                'blocked_reason' => $data['blocked_reason'] ?? null,
                'payload_json' => $data['payload_json'] ?? null,
                'issued_at' => $data['issued_at'] ?? date('Y-m-d H:i:s'),
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<int, string> $issueIdentifiers
     * @return array<string, array<string, mixed>>
     */
    public function findGeneratedByIdentifiers(int $companyId, array $issueIdentifiers): array
    {
        if ($issueIdentifiers === []) {
            return [];
        }

        $result = [];

        foreach (array_chunk(array_values(array_unique($issueIdentifiers)), 500) as $chunk) {
            $placeholders = [];
            $params = ['company_id' => $companyId, 'emission_status' => 'generated'];

            foreach ($chunk as $index => $identifier) {
                $paramKey = 'identifier_' . $index;
                $placeholders[] = ':' . $paramKey;
                $params[$paramKey] = $identifier;
            }

            $statement = $this->execute(
                'SELECT *
                 FROM certificate_issue_registry
                 WHERE company_id = :company_id
                   AND emission_status = :emission_status
                   AND issue_identifier IN (' . implode(', ', $placeholders) . ')
                 ORDER BY issued_at DESC, id DESC',
                $params
            );

            foreach ($statement->fetchAll() as $row) {
                $identifier = (string) $row['issue_identifier'];

                if (!isset($result[$identifier])) {
                    $result[$identifier] = $row;
                }
            }
        }

        return $result;
    }
}
