{{-- ADD / EDIT MENU ITEM (one form for both). $item->exists tells us which mode we are in.
     $vendors is only filled for admins (managers are locked to their own vendor). --}}
@extends('layouts.app')
@section('title', $item->exists ? 'Edit Item' : 'Add Item')

@section('content')
<div class="panel narrow">
    <h1>{{ $item->exists ? '✏️ Edit item' : '🍽️ Add menu item' }}</h1>

    <form method="POST" action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        @if (auth()->user()->isAdmin())
            <label>Vendor
                <select name="vendor_id" required>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected(old('vendor_id', $item->vendor_id) == $vendor->id)>{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <label>Item name <input type="text" name="name" value="{{ old('name', $item->name) }}" required maxlength="100" placeholder="e.g. Chapati Beans"></label>

        <label>Price (KES)
            <input type="number" name="price" value="{{ old('price', $item->price) }}" required min="1" max="5000" step="1" inputmode="numeric">
        </label>

        <label>Category
            <select name="category_id" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->emoji }} {{ $category->name }}</option>
                @endforeach
            </select>
        </label>

        <input type="hidden" name="is_active" value="0">
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->exists ? $item->is_active : 1))> In stock (visible to students)</label>

        <div class="form-actions">
            <button class="btn btn-primary">Save item</button>
            <a class="btn btn-ghost" href="{{ route('items.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
