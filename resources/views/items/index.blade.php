{{-- MENU ITEM LIST (R3 for vendors, R4 for admin).
     Includes a search box + status filter (API brief 5.8) and the CRUD buttons. --}}
@extends('layouts.app')
@section('title', 'Menu Items')

@section('content')
<div class="toolbar">
    <h1>🍽️ {{ auth()->user()->isAdmin() ? 'All Menu Items' : 'My Menu' }}</h1>
    <a href="{{ route('items.create') }}" class="btn btn-primary">+ Add item</a>
</div>

{{-- Search / filter form (GET, so the URL can be shared: /items?q=chapati&status=active) --}}
<form method="GET" action="{{ route('items.index') }}" class="filters panel" style="margin-bottom:1rem">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search item name...">
    <select name="status">
        <option value="">All statuses</option>
        <option value="active" @selected(request('status') === 'active')>In stock</option>
        <option value="inactive" @selected(request('status') === 'inactive')>Out of stock</option>
    </select>
    <button class="btn btn-gold">Filter</button>
    <a href="{{ route('items.index') }}" class="btn btn-ghost">Reset</a>
</form>

<div class="table-wrap">
    <table class="data">
        <thead>
            <tr>
                <th>Item</th>
                @if (auth()->user()->isAdmin()) <th>Vendor</th> @endif
                <th>Category</th><th>Price (KES)</th><th>Stock</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td><strong>{{ $item->category->emoji }} {{ $item->name }}</strong></td>
                @if (auth()->user()->isAdmin())
                    <td>{{ $item->vendor->name }} <span class="muted">({{ $item->vendor->location->name }})</span></td>
                @endif
                <td>{{ $item->category->name }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td><span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">{{ $item->is_active ? 'In stock' : 'Out of stock' }}</span></td>
                <td class="actions">
                    {{-- IsActive toggle --}}
                    <form method="POST" action="{{ route('items.toggle', $item) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-gold">{{ $item->is_active ? 'Mark out' : 'Mark in' }}</button>
                    </form>
                    <a class="btn btn-sm btn-ghost" href="{{ route('items.edit', $item) }}">Edit</a>
                    {{-- data-confirm triggers the "Are you sure?" popup in app.js --}}
                    <form method="POST" action="{{ route('items.destroy', $item) }}" data-confirm="Delete {{ $item->name }}? This cannot be undone.">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No items found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
