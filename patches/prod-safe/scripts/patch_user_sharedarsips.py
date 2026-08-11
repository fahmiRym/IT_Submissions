#!/usr/bin/env python3
"""Inject sharedArsips() method di User.php prod-safe (setelah notifications() method).
Idempoten: skip kalau sudah ada."""
import sys

p = sys.argv[1] if len(sys.argv) > 1 else '/root/it_submissions/app/Models/User.php'
s = open(p).read()

if 'function sharedArsips' in s:
    print('sharedArsips method already exists, skip')
    sys.exit(0)

anchor = 'public function notifications()'
if anchor not in s:
    print('NOTIFICATIONS anchor not found, fallback: append before end of class')
    # Find last closing brace
    s = s.rstrip()
    if s.endswith('}'):
        insert = '''
    /* ===================== SHARED ARSIPS (Y2 M4 deploy 2026-07-30) ===================== */

    public function sharedArsips()
    {
        $userId = $this->id;
        $role = $this->role;
        return \\App\\Models\\Arsip::query()
            ->whereExists(function ($q) use ($userId, $role) {
                $q->select(\\DB::raw(1))
                  ->from('arsip_shares')
                  ->whereColumn('arsip_shares.arsip_id', 'arsips.id')
                  ->where(function ($w) use ($userId, $role) {
                      $w->where(function ($x) use ($userId) {
                          $x->where('arsip_shares.target_type', 'user')
                            ->where('arsip_shares.user_id', $userId);
                      })->orWhere(function ($x) use ($role) {
                          $x->where('arsip_shares.target_type', 'role')
                            ->where('arsip_shares.role', $role);
                      });
                  });
            });
    }
}'''
        s = s[:-1] + insert
        open(p, 'w').write(s)
        print('DONE: appended sharedArsips at end of class')
        sys.exit(0)

# Find balanced '}' after notifications() {
method_start = s.index(anchor)
brace_open = s.index('{', method_start)
depth = 1
i = brace_open + 1
while i < len(s) and depth > 0:
    if s[i] == '{': depth += 1
    elif s[i] == '}': depth -= 1
    i += 1
# i sekarang di posisi setelah '}' penutup method notifications()

insert = '''

    /* ===================== SHARED ARSIPS (Y2 M4 deploy 2026-07-30) ===================== */

    public function sharedArsips()
    {
        $userId = $this->id;
        $role = $this->role;
        return \\App\\Models\\Arsip::query()
            ->whereExists(function ($q) use ($userId, $role) {
                $q->select(\\DB::raw(1))
                  ->from('arsip_shares')
                  ->whereColumn('arsip_shares.arsip_id', 'arsips.id')
                  ->where(function ($w) use ($userId, $role) {
                      $w->where(function ($x) use ($userId) {
                          $x->where('arsip_shares.target_type', 'user')
                            ->where('arsip_shares.user_id', $userId);
                      })->orWhere(function ($x) use ($role) {
                          $x->where('arsip_shares.target_type', 'role')
                            ->where('arsip_shares.role', $role);
                      });
                  });
            });
    }'''

s = s[:i] + insert + s[i:]
open(p, 'w').write(s)
print('DONE: injected sharedArsips after notifications()')
