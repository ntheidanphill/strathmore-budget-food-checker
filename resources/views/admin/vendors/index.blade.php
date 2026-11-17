{{-- ADMIN: LIST OF VENDORS --}}
@extends('layouts.app')
@section('title', 'Vendors')

@section('content')
<div class="toolbar">
    <h1>🏪 Vendors</h1>
    <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">+ Add vendor</a>
</div>

<div class="table-wrap">
    <table class="data">
        <thead><tr><th>Vendor</th><th>Location</th><th>Items</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse ($vendors as $vendor)
            <tr>
                <td><strong>{{ $vendor->name }}</strong></td>
                <td>{{ $vendor->location->name }}</td>
                <td>{{ $vendor->menu_items_count }}</td>
                <td><span class="badge {{ $vendor->is_active ? 'badge-on' : 'badge-off' }}">{{ $vendor->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a class="btn btn-sm btn-ghost" href="{{ route('admin.vendors.edit', $vendor) }}">Edit</a>
                    {{-- PATCH toggle: HTML forms only do GET/POST so @method('PATCH') fakes it --}}
                    <form method="POST" action="{{ route('admin.vendors.toggle', $vendor) }}"
                          data-confirm="{{ $vendor->is_active ? 'Deactivate' : 'Activate' }} {{ $vendor->name }}?">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm {{ $vendor->is_active ? 'btn-danger' : 'btn-green' }}">{{ $vendor->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No vendors yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
