<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return Asset::query()
            ->with(['category', 'location', 'employee'])
            ->when($this->request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            })
            ->when($this->request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Asset Tag',
            'Serial Number',
            'Nama Aset',
            'Kategori',
            'Lokasi',
            'Brand',
            'Model',
            'Vendor',
            'Tahun Beli',
            'Garansi (Thn)',
            'Expired Garansi',
            'Status',
            'Kondisi Fisik', // <--- KOLOM BARU
            'Peminjam (Pegawai)',
            'Tipe Pinjam',
            'Tgl Wajib Kembali',
            'Terdaftar Pada',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->asset_tag,
            $asset->serial_number,
            $asset->name,
            $asset->category->name ?? '-',
            $asset->location->name ?? '-',
            $asset->brand,
            $asset->model,
            $asset->vendor,
            $asset->purchase_year,
            $asset->period,
            $asset->expiry_year,
            $asset->status,
            $asset->condition,
            $asset->employee->name ?? '-',
            $asset->loan_type ?? '-',
            $asset->due_date ? $asset->due_date->format('d-m-Y') : '-',
            $asset->created_at->format('d-m-Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}