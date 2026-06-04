<?php

namespace App\Console\Commands;

use App\Models\Unit;
use App\Models\Department;
use App\Models\User;
use App\Models\UsersStaging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApplyUserImportCommand extends Command
{
    protected $signature = 'users:apply-import
                            {--batch= : Batch ID (kosong = semua status=pending/reviewed)}
                            {--dry-run : Hanya simulasi, jangan tulis ke DB}
                            {--deactivate-missing : Set is_active=0 untuk user legacy yang tidak ada di staging batch ini}';

    protected $description = 'Terapkan hasil matching: update existing user (tambah employee_id + work_unit) atau buat user baru.';

    public function handle(): int
    {
        $batch = $this->option('batch');
        $dry = (bool) $this->option('dry-run');
        $deactivateMissing = (bool) $this->option('deactivate-missing');

        $query = UsersStaging::query()->whereIn('status', ['pending', 'reviewed']);
        if ($batch) {
            $query->where('batch_id', $batch);
        }
        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->warn('Tidak ada staging row yang siap di-apply.');
            return self::SUCCESS;
        }

        // GUARD: auto-match harus sudah jalan
        $unmatched = $rows->whereNull('match_method')->count();
        if ($unmatched > 0) {
            $this->error("{$unmatched} row belum di-auto-match. Jalankan dulu:");
            $this->line('  php artisan users:auto-match' . ($batch ? " --batch={$batch}" : ''));
            return self::FAILURE;
        }

        // GUARD: cek duplikat employee_id di dalam staging itu sendiri (data Excel kotor)
        $dupEmpIds = $rows->groupBy('employee_id')
            ->filter(fn($g) => $g->count() > 1)
            ->keys()
            ->all();
        if (!empty($dupEmpIds)) {
            $this->warn('Ditemukan employee_id duplikat di staging (akan diambil row pertama saja): '
                . implode(', ', array_slice($dupEmpIds, 0, 10))
                . (count($dupEmpIds) > 10 ? ' ... (+' . (count($dupEmpIds) - 10) . ' lainnya)' : ''));
            // Ambil 1 row per employee_id (yang punya matched_user_id diprioritaskan, sisanya skipped)
            $rows = $rows->sortByDesc(fn($r) => $r->matched_user_id ? 1 : 0)
                ->unique('employee_id')
                ->values();
        }

        $this->info(($dry ? '[DRY-RUN] ' : '') . "Memproses {$rows->count()} row staging ...");
        $stats = ['updated' => 0, 'created' => 0, 'skipped' => 0, 'deactivated' => 0];

        DB::beginTransaction();
        try {
            foreach ($rows as $staging) {
                $unitId = $this->resolveUnitId($staging->work_unit_name, $dry);
                $deptId = $this->resolveDepartmentId($staging->department_name, $dry);

                if ($staging->matched_user_id) {
                    // UPDATE existing — hanya isi field yang masih kosong (anti-overwrite)
                    $user = User::find($staging->matched_user_id);
                    if (!$user) {
                        $stats['skipped']++;
                        continue;
                    }

                    $changes = array_filter([
                        'employee_id'    => $user->employee_id ?: $staging->employee_id,
                        'work_unit_id'   => $user->work_unit_id ?: $unitId,
                        'department_id'  => $user->department_id ?: $deptId,
                        'last_synced_at' => now(),
                    ]);

                    if (!$dry) {
                        $user->fill($changes)->save();
                    }
                    $stats['updated']++;
                } else {
                    // SAFETY: cek employee_id sudah ada di users (bisa karena run sebelumnya)
                    $existingByEmp = User::where('employee_id', $staging->employee_id)->first();
                    if ($existingByEmp) {
                        if (!$dry) {
                            $existingByEmp->fill(array_filter([
                                'work_unit_id'   => $existingByEmp->work_unit_id ?: $unitId,
                                'department_id'  => $existingByEmp->department_id ?: $deptId,
                                'last_synced_at' => now(),
                            ]))->save();
                            $staging->matched_user_id = $existingByEmp->id;
                            $staging->match_method = 'employee_id';
                        }
                        $stats['updated']++;
                        if (!$dry) {
                            $staging->status = 'applied';
                            $staging->save();
                        }
                        continue;
                    }

                    // CREATE new user — pakai staging.id sebagai suffix bila collision
                    $username = 'nik_' . $staging->employee_id;
                    $suffix = 1;
                    while (!$dry && User::where('username', $username)->exists()) {
                        $username = 'nik_' . $staging->employee_id . '_' . $staging->id . ($suffix > 1 ? "_{$suffix}" : '');
                        $suffix++;
                        if ($suffix > 5) break; // safety
                    }

                    $email = $staging->employee_id . '@placeholder.local';
                    if (!$dry && User::where('email', $email)->exists()) {
                        $email = $staging->employee_id . '.' . $staging->id . '@placeholder.local';
                    }

                    if (!$dry) {
                        User::create([
                            'employee_id'          => $staging->employee_id,
                            'name'                 => $staging->name,
                            'username'             => $username,
                            'email'                => $email,
                            'password'             => Hash::make($staging->employee_id),
                            'role'                 => 'admin',
                            'department_id'        => $deptId,
                            'work_unit_id'         => $unitId,
                            'is_active'            => true,
                            'source'               => 'hr_import',
                            'must_change_password' => true,
                            'last_synced_at'       => now(),
                        ]);
                    }
                    $stats['created']++;
                }

                if (!$dry) {
                    $staging->status = 'applied';
                    $staging->save();
                }
            }

            // Deactivate legacy users yang tidak ada di batch ini
            if ($deactivateMissing && $batch) {
                $matchedIds = UsersStaging::where('batch_id', $batch)
                    ->whereNotNull('matched_user_id')
                    ->pluck('matched_user_id')
                    ->all();

                $toDeactivate = User::query()
                    ->whereNull('employee_id')
                    ->where('source', 'legacy')
                    ->where('is_active', true)
                    ->where('role', '!=', 'superadmin')
                    ->whereNotIn('id', $matchedIds)
                    ->get();

                foreach ($toDeactivate as $u) {
                    if (!$dry) {
                        $u->is_active = false;
                        $u->save();
                    }
                    $stats['deactivated']++;
                }
            }

            if ($dry) {
                DB::rollBack();
                $this->warn('DRY-RUN: rollback semua perubahan.');
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('GAGAL: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->line('');
        $this->table(
            ['Action', 'Count'],
            collect($stats)->map(fn($v, $k) => [$k, $v])->values()->all()
        );
        $this->info('Selesai.');
        return self::SUCCESS;
    }

    private function resolveUnitId(?string $name, bool $dry): ?int
    {
        if (!$name) return null;
        $name = trim($name);
        $unit = Unit::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if ($unit) return $unit->id;
        if ($dry) return null;
        return Unit::create(['name' => $name, 'is_active' => true])->id;
    }

    private function resolveDepartmentId(?string $name, bool $dry): ?int
    {
        if (!$name) return null;
        $name = trim($name);
        $dept = Department::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if ($dept) return $dept->id;
        if ($dry) return null;
        return Department::create(['name' => $name])->id;
    }
}
