<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arsip;
use Carbon\Carbon;

class ArsipSeeder extends Seeder
{
    public function run(): void
    {
        Arsip::create([
            'tgl_pengajuan' => Carbon::now()->subDays(3),
            'tgl_arsip'     => null,

            'admin_id'      => 2, // pastikan user admin ID 2 ada
            'superadmin_id' => null,

            'department_id' => 1,
            'manager_id'    => 1,
            'unit_id'       => 1,

            'eror'          => 'Human',
            'ket_eror'      => 'Salah input nomor transaksi',

            'no_doc'        => null,
            'no_transaksi'  => "MO/PF/25/10/105867\nINK/PR/25700803\nBPB-25/12/0100",

            'ba'           => 'Process',
            'arsip'        => 'Process',
            'ket_process'  => 'Process',
            'status'       => 'Process',

            'bukti_scan'   => null
        ]);
    }
}
