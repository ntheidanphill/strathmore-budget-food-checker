{{-- ADMIN: ONE form used for BOTH "add vendor" and "edit vendor".
     $vendor->exists is true when editing, false when adding. --}}
@extends('layouts.app')
@section('title', $vendor->exists ? 'Edit Vendor' : 'Add Vendor')

@section('content')
<div class="panel narrow">
    <h1>{{ $vendor->exists ? '✏️ Edit vendor' : '🏪 Add vendor' }}</h1>

    <form method="POST" action="{{ $vendor->exists ? route('admin.vendors.update', $vendor) : route('admin.vendors.store') }}">
        @csrf
        @if ($vendor->exists) @method('PUT') @endif

        <label>Vendor name
            {{-- old() keeps what the user typed if validation fails --}}
            <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required maxlength="100">
        </label>

        <label>Location
            <select name="location_id" required>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(old('location_id', $vendor->location_id) == $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </label>

        {{-- hidden 0 + checkbox 1: an unticked box still sends a value --}}
        <input type="hidden" name="is_active" value="0">
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vendor->exists ? $vendor->is_active : 1))> Active (visible to students)</label>

        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a class="btn btn-ghost" href="{{ route('admin.vendors.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
