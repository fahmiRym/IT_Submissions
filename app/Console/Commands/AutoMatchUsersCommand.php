<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UsersStaging;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AutoMatchUsersCommand extends Command
{
    protected $signature = 'users:auto-match
                            {--batch= : Batch ID dari import (kosong = semua status=pending)}
                            {--fuzzy-threshold=85 : Threshold similar_text (0..100) untuk fuzzy match}';

    protected $description = 'Auto-match users_staging ke users existing (by employee_id → exact name → fuzzy name).';

    public function handle(): int
    {
        $batch = $this->option('batch');
        $threshold = (int) $this->option('fuzzy-threshold');

        $query = UsersStaging::query()->where('status', 'pending');
        if ($batch) {
            $query->where('batch_id', $batch);
        }

        $stagingRows = $query->get();
        if ($stagingRows->isEmpty()) {
            $this->warn('Tidak ada staging row dengan status=pending.');
            return self::SUCCESS;
        }

        $this->info("Memproses {$stagingRows->count()} row staging ...");

        // Preload semua users existing satu kali (lebih cepat untuk fuzzy)
        $allUsers = User::query()
            ->select('id', 'name', 'employee_id', 'username')
            ->get();

        $stats = [
            'employee_id' => 0,
            'exact_name'  => 0,
            'fuzzy_name'  => 0,
            'new'         => 0,
        ];

        $bar = $this->output->createProgressBar($stagingRows->count());
        $bar->start();

        foreach ($stagingRows as $staging) {
            $matched = $this->matchByEmployeeId($staging, $allUsers)
                ?? $this->matchByExactName($staging, $allUsers)
                ?? $this->matchByFuzzyName($staging, $allUsers, $threshold);

            if ($matched) {
                $staging->matched_user_id = $matched['user_id'];
                $staging->match_method    = $matched['method'];
                $staging->match_score     = $matched['score'];
            } else {
                $staging->matched_user_id = null;
                $staging->match_method    = 'new';
                $staging->match_score     = 0;
            }
            $staging->save();

            $stats[$matched['method'] ?? 'new']++;
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('');
        $this->info('Hasil match:');
        $this->table(
            ['Method', 'Count'],
            collect($stats)->map(fn($v, $k) => [$k, $v])->values()->all()
        );
        $this->line('');
        $this->line('Langkah berikutnya:');
        $this->line('  Review manual di /superadmin/users/import-review');
        $this->line('  Kemudian: php artisan users:apply-import' . ($batch ? " --batch={$batch}" : ''));

        return self::SUCCESS;
    }

    private function matchByEmployeeId(UsersStaging $staging, Collection $allUsers): ?array
    {
        $user = $allUsers->firstWhere('employee_id', $staging->employee_id);
        return $user ? ['user_id' => $user->id, 'method' => 'employee_id', 'score' => 100] : null;
    }

    private function matchByExactName(UsersStaging $staging, Collection $allUsers): ?array
    {
        $needle = $this->normalize($staging->name);
        foreach ($allUsers as $u) {
            if ($this->normalize($u->name) === $needle) {
                return ['user_id' => $u->id, 'method' => 'exact_name', 'score' => 100];
            }
        }
        return null;
    }

    private function matchByFuzzyName(UsersStaging $staging, Collection $allUsers, int $threshold): ?array
    {
        $needle = $this->normalize($staging->name);
        $best = null;
        foreach ($allUsers as $u) {
            similar_text($needle, $this->normalize($u->name), $pct);
            if ($pct >= $threshold && (!$best || $pct > $best['score'])) {
                $best = ['user_id' => $u->id, 'method' => 'fuzzy_name', 'score' => (int) round($pct)];
            }
        }
        return $best;
    }

    private function normalize(string $s): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($s)));
    }
}
