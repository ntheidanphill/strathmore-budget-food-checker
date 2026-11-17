{{-- ADMIN: add / edit a Vendor Manager account (same form for both) --}}
@extends('layouts.app')
@section('title', $manager->exists ? 'Edit Manager' : 'Add Manager')

@section('content')
<div class="panel narrow">
    <h1>{{ $manager->exists ? '✏️ Edit manager' : '👨‍🍳 Add manager' }}</h1>

    <form method="POST" action="{{ $manager->exists ? route('admin.managers.update', $manager) : route('admin.managers.store') }}">
        @csrf
        @if ($manager->exists) @method('PUT') @endif

        <label>Full name <input type="text" name="name" value="{{ old('name', $manager->name) }}" required></label>
        <label>Username <input type="text" name="username" value="{{ old('username', $manager->username) }}" required></label>
        <label>Email <input type="email" name="email" value="{{ old('email', $manager->email) }}" required></label>

        <label>Vendor they manage
            <select name="vendor_id" required>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(old('vendor_id', $manager->vendor_id) == $vendor->id)>{{ $vendor->name }}</option>
                @endforeach
            </select>
        </label>

        <label>Password {{ $manager->exists ? '(leave blank to keep the current one)' : '' }}
            <input type="password" name="password" {{ $manager->exists ? '' : 'required' }} minlength="8" autocomplete="new-password">
        </label>
        <label>Confirm password <input type="password" name="password_confirmation" autocomplete="new-password"></label>

        <input type="hidden" name="is_active" value="0">
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $manager->exists ? $manager->is_active : 1))> Account active</label>

        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a class="btn btn-ghost" href="{{ route('admin.managers.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
