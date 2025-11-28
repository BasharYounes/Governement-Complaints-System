<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ComplaintsExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected Collection $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection->map(function ($c) {
            return [
                $c->reference_number,
                $c->description,
                $c->status,
                $c->governmentEntity->name ?? '',
                $c->user->name ?? '',
                $c->created_at?->format('Y-m-d H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Description',
            'Status',
            'Government Entity',
            'User',
            'Created At',
        ];
    }

   
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\r\n",
            'use_bom' => true,
        ];
    }
}
