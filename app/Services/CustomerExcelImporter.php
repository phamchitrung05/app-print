<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class CustomerExcelImporter
{
    /**
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function import(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['File Excel không có dữ liệu để import.'],
            ];
        }

        $headerRow = array_shift($rows);
        $headers = $this->normalizeHeaders($headerRow);
        $requiredColumns = ['name', 'phone'];

        foreach ($requiredColumns as $column) {
            if (! in_array($column, $headers, true)) {
                return [
                    'imported' => 0,
                    'skipped' => 0,
                    'errors' => ["File Excel thiếu cột bắt buộc: {$column}."],
                ];
            }
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $headers, &$imported, &$skipped, &$errors): void {
            foreach ($rows as $rowNumber => $row) {
                $excelRow = $rowNumber + 2;
                $data = $this->mapRow($row, $headers);

                if ($this->isBlankRow($data)) {
                    $skipped++;

                    continue;
                }

                if (blank($data['name'] ?? null) || blank($data['phone'] ?? null)) {
                    $errors[] = "Dòng {$excelRow}: bắt buộc có Họ và tên và Số điện thoại.";

                    continue;
                }

                try {
                    Customer::create([
                        'uuid' => (string) Str::uuid(),
                        'name' => trim((string) $data['name']),
                        'phone' => trim((string) $data['phone']),
                        'address' => $this->nullableString($data['address'] ?? null),
                        'note' => $this->nullableString($data['note'] ?? null),
                        'is_active' => $this->parseBoolean($data['is_active'] ?? null),
                    ]);

                    $imported++;
                } catch (Throwable $exception) {
                    $errors[] = "Dòng {$excelRow}: không thể lưu dữ liệu ({$exception->getMessage()}).";
                }
            }
        });

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * @param  array<string|int, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapRow(array $row, array $headers): array
    {
        $data = [];

        foreach ($headers as $columnIndex => $header) {
            $data[$header] = $row[$columnIndex] ?? null;
        }

        return $data;
    }

    /**
     * @param  array<string|int, mixed>  $headerRow
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        $headers = [];

        foreach ($headerRow as $columnIndex => $header) {
            $normalized = Str::of((string) $header)
                ->lower()
                ->ascii()
                ->replace([' ', '-', '_'], '')
                ->toString();

            $headers[$columnIndex] = match ($normalized) {
                'name', 'hoten', 'hovaten' => 'name',
                'phone', 'sodienthoai', 'dienthoai' => 'phone',
                'address', 'diachi' => 'address',
                'note', 'ghichu' => 'note',
                'isactive', 'active', 'trangthai' => 'is_active',
                default => $normalized,
            };
        }

        return $headers;
    }

    private function isBlankRow(array $data): bool
    {
        return collect($data)->every(fn (mixed $value): bool => blank($value));
    }

    private function nullableString(mixed $value): ?string
    {
        return blank($value) ? null : trim((string) $value);
    }

    private function parseBoolean(mixed $value): bool
    {
        if (blank($value)) {
            return true;
        }

        return in_array(
            Str::lower(trim((string) $value)),
            ['1', 'true', 'yes', 'y', 'active', 'dang hoat dong', 'đang hoạt động'],
            true,
        );
    }
}
