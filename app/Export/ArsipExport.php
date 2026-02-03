<?php

namespace App\Exports;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArsipExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Arsip::with('user','department');

        if ($this->request->from && $this->request->to) {
            $query->whereBetween('tgl_pengajuan', [
                $this->request->from,
                $this->request->to
            ]);
        }

        if ($this->request->department_id) {
            $query->where('department_id', $this->request->department_id);
        }

        return $query->get()->map(function ($a) {
            return [
                $a->tgl_pengajuan,
                $a->user->name ?? '-',
                $a->department->name ?? '-',
                $a->status
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal Pengajuan',
            'User',
            'Departemen',
            'Status'
        ];
    }
}
