{{-- STAFF LOGIN PAGE - for Vendor Managers and Administrators only (students search as guests) --}}
@extends('layouts.app')
@section('title', 'Staff Login')

@section('content')
<div class="panel narrow">
    <div class="login-art">🍲🔐</div>
    <h1>Staff Login</h1>
    <p class="muted">Vendor Managers and Administrators only. Students don't need an account —
        <a href="{{ route('search') }}">search for food here</a>.</p>

    {{-- @csrf adds a hidden token that protects the form against forgery --}}
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <label>Username
            <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
        </label>
        <label>Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Log in</button>
        </div>
    </form>
</div>
@endsection
