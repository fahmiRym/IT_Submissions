<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $candidates = DB::table('users')
            ->where('username', 'LIKE', 'nik\_%')
            ->get(['id', 'username', 'employee_id']);

        foreach ($candidates as $row) {
            $stripped = preg_replace('/^nik_/', '', $row->username);
            if ($stripped === '' || $stripped === null) continue;

            $exists = DB::table('users')
                ->where('username', $stripped)
                ->where('id', '!=', $row->id)
                ->exists();

            if ($exists) {
                $stripped = $stripped . '_' . $row->id;
            }

            DB::table('users')->where('id', $row->id)->update(['username' => $stripped]);
        }
    }

    public function down(): void
    {
        // No-op: tidak praktis mengembalikan prefix `nik_` tanpa kehilangan info collision-suffix.
    }
};
