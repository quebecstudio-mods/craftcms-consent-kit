<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Web;

use PhpOffice\PhpSpreadsheet\Spreadsheet as Workbook;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rows of the same shape, written as a download. PhpSpreadsheet ships with
 * Craft CMS, so a real workbook costs no extra dependency.
 */
abstract class Spreadsheet
{
    public static function csv(array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            if ($rows !== []) {
                fputcsv($handle, array_keys($rows[0]));
            }

            foreach ($rows as $row) {
                fputcsv($handle, array_values($row));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public static function xlsx(array $rows, string $filename): StreamedResponse
    {
        $workbook = new Workbook();
        $sheet = $workbook->getActiveSheet();

        if ($rows !== []) {
            $sheet->fromArray(array_keys($rows[0]), null, 'A1');
            $sheet->fromArray(array_map(array_values(...), $rows), null, 'A2');
        }

        return response()->streamDownload(function () use ($workbook): void {
            new Xlsx($workbook)->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
