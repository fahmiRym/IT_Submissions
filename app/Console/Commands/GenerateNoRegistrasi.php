<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Arsip;

class GenerateNoRegistrasi extends Command
{
    protected $signature = 'arsip:generate-no-registrasi';
    protected $description = 'Generate no_registrasi untuk data arsip lama yang masih kosong';

    public function handle()
    {
        $arsips = Arsip::whereNull('no_registrasi')->get();

        if ($arsips->isEmpty()) {
            $this->info('✅ Semua arsip sudah memiliki no registrasi.');
            return;
        }

        $this->info("🔄 Memproses {$arsips->count()} data arsip...");

// Group by date to handle sequence correctly in batch
        $groupedArsips = $arsips->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        foreach ($groupedArsips as $dateStr => $items) {
            
            // Get initial sequence for this date
            $currentSeq = Arsip::whereDate('created_at', $dateStr)
                ->whereNotNull('no_registrasi')
                ->count();

            $this->info("📅 Processing date: {$dateStr} (Start Seq: {$currentSeq})");

            foreach ($items as $arsip) {
                // ❌ skip kalau relasi wajib kosong
                if (!$arsip->department || !$arsip->unit) {
                    $this->warn("⏭ Skip ID {$arsip->id} (department/unit kosong)");
                    continue;
                }

                // === KOMPONEN FORMAT ===
                $deptCode = strtoupper(substr($arsip->department->name, 0, 3));
                $unitCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', $arsip->unit->name));
                $date     = $arsip->created_at->format('ymd');

                // === URUTAN PER HARI (INCREMENT MEMORY) ===
                $currentSeq++;
                
                $noRegistrasi = sprintf(
                    '%s-%s-%s-%03d',
                    $deptCode,
                    $date,
                    $unitCode,
                    $currentSeq
                );

                // 🔥 BYPASS MODEL EVENT & IMMUTABLE CHECK (Wajib pakai DB::table)
                \Illuminate\Support\Facades\DB::table('arsips')
                    ->where('id', $arsip->id)
                    ->update([
                        'no_registrasi' => $noRegistrasi,
                        'updated_at'    => now()
                    ]);

                $this->line("✔ ID {$arsip->id} → {$noRegistrasi}");
            }
        }

        $this->info('🎉 Generate no registrasi selesai!');
    }
}
