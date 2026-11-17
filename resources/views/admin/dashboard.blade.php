{{-- ADMIN HOME: counts (animated by app.js data-count) + shortcut tiles --}}
@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<h1>⚙️ Administrator Dashboard</h1>
<p class="muted">Welcome, {{ auth()->user()->name }}. Manage everything that students see.</p>

<div class="stats">
    @php $i = 0; @endphp
    @foreach (['vendors' => 'Vendors', 'managers' => 'Vendor Managers', 'locations' => 'Locations', 'categories' => 'Categories', 'items' => 'Menu Items'] as $key => $label)
        <div class="stat" style="--i: {{ $i++ }}">
            <div class="num" data-count="{{ $counts[$key] }}">0</div>
            <div class="lbl">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="tiles">
    <a class="tile" href="{{ route('admin.vendors.index') }}"><span class="ico">🏪</span><strong>Vendors</strong><br><span class="muted">Add, edit, deactivate</span></a>
    <a class="tile" href="{{ route('admin.managers.index') }}"><span class="ico">👨‍🍳</span><strong>Vendor Managers</strong><br><span class="muted">Accounts &amp; access</span></a>
    <a class="tile" href="{{ route('admin.locations.index') }}"><span class="ico">📍</span><strong>Locations</strong><br><span class="muted">Student Centre, Siwaka...</span></a>
    <a class="tile" href="{{ route('admin.categories.index') }}"><span class="ico">🏷️</span><strong>Food Categories</strong><br><span class="muted">Breakfast, Lunch...</span></a>
    <a class="tile" href="{{ route('items.index') }}"><span class="ico">🍽️</span><strong>All Menu Items</strong><br><span class="muted">Correct items &amp; prices</span></a>
</div>
@endsection
