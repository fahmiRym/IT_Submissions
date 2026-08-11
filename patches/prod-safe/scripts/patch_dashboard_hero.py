#!/usr/bin/env python3
"""Guard admin.approvals di dashboard hero partial."""
import sys, re

p = sys.argv[1] if len(sys.argv) > 1 else '/root/it_submissions/resources/views/partials/_dashboard_hero.blade.php'
s = open(p).read()

# Match multi-line block admin approvals
old = '''                <a href="{{ route('admin.approvals.index') }}" class="dash-quick-action position-relative">
                    <i class="bi bi-check2-square"></i> Persetujuan Saya
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span class="badge bg-danger ms-1">{{ $pendingApprovalCount }}</span>
                    @endif
                </a>'''

new = '''                @if(Route::has('admin.approvals.index'))
                <a href="{{ route('admin.approvals.index') }}" class="dash-quick-action position-relative">
                    <i class="bi bi-check2-square"></i> Persetujuan Saya
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span class="badge bg-danger ms-1">{{ $pendingApprovalCount }}</span>
                    @endif
                </a>
                @endif'''

if 'Route::has(\'admin.approvals.index\')' in s:
    print('already guarded')
elif old not in s:
    print('OLD BLOCK NOT FOUND')
else:
    s = s.replace(old, new)
    open(p, 'w').write(s)
    print('DONE: wrapped admin.approvals with Route::has guard')
