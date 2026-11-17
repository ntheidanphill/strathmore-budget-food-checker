{{-- VENDOR MANAGER DASHBOARD (R3): quick summary of this vendor's menu.
     $vendor, $stats and $recent come from Vendor\DashboardController. --}}
@extends('layouts.app')
@section('title', 'Vendor Dashboard')

@section('content')
<h1>📊 {{ $vendor->name }}</h1>
<p class="muted">📍 {{ $vendor->location->name }} · Logged in as {{ auth()->user()->name }}</p>

{{-- Metric cards. data-count makes the number count up (see public/js/app.js) --}}
<div class="stats">
    <div class="stat" style="--i:0"><div class="num" data-count="{{ $stats['total'] }}">0</div><div class="lbl">Total items</div></div>
    <div class="stat" style="--i:1"><div class="num" data-count="{{ $stats['active'] }}">0</div><div class="lbl">In stock (active)</div></div>
    <div class="stat" style="--i:2"><div class="num" data-count="{{ $stats['inactive'] }}">0</div><div class="lbl">Out of stock</div></div>
    <div class="stat" style="--i:3"><div class="num">KES <span data-count="{{ $stats['average'] }}">0</span></div><div class="lbl">Average price</div></div>
</div>

<div class="toolbar">
    <h2>Recently updated</h2>
    <div class="actions">
        <a href="{{ route('items.create') }}" class="btn btn-primary">+ Add item</a>
        <a href="{{ route('items.index') }}" class="btn btn-ghost">Manage full menu</a>
    </div>
</div>

<div class="grid">
    @forelse ($recent as $item)
        <div class="card" style="--i: {{ $loop->index }}">
            <span class="emoji">{{ $item->category->emoji }}</span>
            <span class="price">KES {{ number_format($item->price, 0) }}</span>
            <h3>{{ $item->name }}</h3>
            <span class="tag">{{ $item->category->name }}</span>
            <span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">{{ $item->is_active ? 'In stock' : 'Out of stock' }}</span>
        </div>
    @empty
        <div class="empty" style="grid-column: 1/-1"><div class="big">🍽️</div><p>No items yet. Add your first menu item!</p></div>
    @endforelse
</div>
@endsection
