{{-- ADMIN: LIST OF VENDOR MANAGER ACCOUNTS --}}
@extends('layouts.app')
@section('title', 'Vendor Managers')

@section('content')
<div class="toolbar">
    <h1>👨‍🍳 Vendor Managers</h1>
    <a href="{{ route('admin.managers.create') }}" class="btn btn-primary">+ Add manager</a>
</div>

<div class="table-wrap">
    <table class="data">
        <thead><tr><th>Name</th><th>Username</th><th>Vendor</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse ($managers as $manager)
            <tr>
                <td>{{ $manager->name }}</td>
                <td>{{ $manager->username }}</td>
                <td>{{ $manager->vendor?->name ?? '—' }}</td>
                <td><span class="badge {{ $manager->is_active ? 'badge-on' : 'badge-off' }}">{{ $manager->is_active ? 'Active' : 'Deactivated' }}</span></td>
                <td class="actions">
                    <a class="btn btn-sm btn-ghost" href="{{ route('admin.managers.edit', $manager) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.managers.toggle', $manager) }}"
                          data-confirm="{{ $manager->is_active ? 'Deactivate' : 'Activate' }} {{ $manager->username }}?">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm {{ $manager->is_active ? 'btn-danger' : 'btn-green' }}">{{ $manager->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No vendor managers yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
