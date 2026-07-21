<?php

namespace App\Traits;

use App\Models\Arsip;
use App\Models\ArsipRequester;
use App\Models\User;

trait SyncsRequesters
{
    /**
     * Sync multi-pemohon ke pivot arsip_requesters.
     * Pertama dianggap pemohon utama (is_primary=true).
     *
     * @param Arsip $arsip
     * @param array $requesterIds  array of user_id (integers / strings)
     * @return string  text gabungan nama (untuk backward-compat arsips.pemohon)
     */
    protected function syncArsipRequesters(Arsip $arsip, array $requesterIds): string
    {
        $requesterIds = array_values(array_unique(array_filter(array_map('intval', $requesterIds))));

        // Clear existing pivot (idempotent untuk update)
        ArsipRequester::where('arsip_id', $arsip->id)->delete();

        if (empty($requesterIds)) {
            return '';
        }

        $users = User::whereIn('id', $requesterIds)
            ->get(['id', 'name', 'employee_id'])
            ->keyBy('id');

        $rows = [];
        $names = [];
        $first = true;
        foreach ($requesterIds as $uid) {
            $u = $users->get($uid);
            if (!$u) continue;

            $rows[] = [
                'arsip_id'       => $arsip->id,
                'user_id'        => $u->id,
                'employee_id'    => (string) ($u->employee_id ?? ''),
                'name_snapshot'  => $u->name,
                'is_primary'     => $first,
                'created_at'     => now(),
            ];
            $names[] = $u->name;
            $first = false;
        }

        if (!empty($rows)) {
            ArsipRequester::insert($rows);
        }

        return implode(', ', $names);
    }
}
