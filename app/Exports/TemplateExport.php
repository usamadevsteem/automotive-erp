<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        private readonly array $headings,
        private readonly array $exampleRows = [],
    ) {}

    public function title(): string
    {
        return 'Import Template';
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->exampleRows;
    }

    public function styles(Worksheet $sheet): array
    {
        // Style header row
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A56DB'],
                ],
            ],
        ];

        // Style example row as light grey
        if (!empty($this->exampleRows)) {
            $styles[2] = [
                'font' => ['italic' => true, 'color' => ['argb' => 'FF6B7280']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF3F4F6'],
                ],
            ];
        }

        return $styles;
    }
}
