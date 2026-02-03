@extends('layouts.app')

@section('title','Notifikasi')
@section('page-title','🔔 Notifikasi')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

        @forelse($notifications as $n)
            <div class="border-bottom py-3 {{ $n->is_read ? '' : 'bg-light' }}">
                <div class="fw-semibold">{{ $n->title }}</div>
                <div class="text-muted small">{{ $n->message }}</div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-secondary">
                        {{ $n->created_at->diffForHumans() }}
                    </small>

                    @if(!$n->is_read)
                        <form method="POST" action="{{ route('notifications.read',$n->id) }}">
                            @csrf
                            @method('PUT')
                            <button class="btn btn-sm btn-outline-primary">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-muted">
                Tidak ada notifikasi
            </div>
        @endforelse

    </div>
</div>

<div class="mt-3">
    {{ $notifications->links('pagination::bootstrap-5') }}
</div>

@endsection
