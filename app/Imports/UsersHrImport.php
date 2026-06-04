<?php

namespace App\Imports;

use App\Models\UsersStaging;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class UsersHrImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    public function __construct(private string $batchId)
    {
    }

    /**
     * Accept rows with headers: employeeId, name, departmentName, workUnitName
     * (any casing — WithHeadingRow normalizes to snake_case).
     */
    public function model(array $row)
    {
        $employeeId = trim((string) ($row['employeeid'] ?? $row['employee_id'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        if ($employeeId === '' || $name === '') {
            return null;
        }

        return new UsersStaging([
            'employee_id'     => $employeeId,
            'name'            => $name,
            'department_name' => trim((string) ($row['departmentname'] ?? $row['department_name'] ?? '')) ?: null,
            'work_unit_name'  => trim((string) ($row['workunitname'] ?? $row['work_unit_name'] ?? '')) ?: null,
            'status'          => 'pending',
            'batch_id'        => $this->batchId,
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
