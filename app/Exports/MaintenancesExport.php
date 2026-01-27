<?php

namespace App\Exports;

use App\Models\Maintenance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaintenancesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Maintenance::query()->with(['asset', 'user']); // Load relasi asset dan user

        // Logika Filter Search (Sama seperti di Controller)
        if ($this->request->search) {
            $search = $this->request->search;
            $query->where('description', 'like', '%' . $search . '%')
                  ->orWhereHas('asset', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('asset_tag', 'like', '%' . $search . '%');
                  });
        }

        // Logika Filter Status
        if ($this->request->status) {
            $query->where('status', $this->request->status);
        }

        return $query->orderBy('scheduled_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Asset Tag',
            'Nama Aset',
            'Deskripsi Perbaikan',
            'Status',
            'Jadwal Perbaikan',
            'Selesai Pada',
            'Dibuat Oleh',
            'Tanggal Input',
        ];
    }

    public function map($maintenance): array
    {
        return [
            $maintenance->id,
            $maintenance->asset->asset_tag ?? '-',
            $maintenance->asset->name ?? '-',
            $maintenance->description,
            ucfirst($maintenance->status), // Kapitalisasi huruf pertama
            $maintenance->scheduled_at ? \Carbon\Carbon::parse($maintenance->scheduled_at)->format('d-m-Y') : '-',
            $maintenance->completed_at ? \Carbon\Carbon::parse($maintenance->completed_at)->format('d-m-Y H:i') : '-',
            $maintenance->user->name ?? 'System', // Asumsi ada relasi user
            $maintenance->created_at->format('d-m-Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Bold baris header
        ];
    }
}