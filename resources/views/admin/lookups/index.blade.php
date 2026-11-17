{{-- SHARED VIEW for Locations AND Categories (both are just "a name + active flag").
     The controller passes: $title, $icon, $routePrefix and $items. --}}
@extends('layouts.app')
@section('title', $title)

@section('content')
<h1>{{ $icon }} {{ $title }}</h1>

{{-- Add new --}}
<form method="POST" action="{{ route($routePrefix . '.store') }}" class="inline-form">
    @csrf
    <input type="text" name="name" placeholder="New name..." required maxlength="80">
    <button class="btn btn-primary">+ Add</button>
</form>

<div class="table-wrap">
    <table class="data">
        <thead><tr><th>Name (edit &amp; save)</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @foreach ($items as $item)
            <tr>
                <td>
                    {{-- Each row has its own small edit form --}}
                    <form method="POST" action="{{ route($routePrefix . '.update', $item) }}" class="inline-form" style="margin:0">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $item->name }}" required maxlength="80">
                        <button class="btn btn-sm btn-ghost">Save</button>
                    </form>
                </td>
                <td><span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                    <form method="POST" action="{{ route($routePrefix . '.toggle', $item) }}"
                          data-confirm="{{ $item->is_active ? 'Deactivate' : 'Activate' }} {{ $item->name }}?">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm {{ $item->is_active ? 'btn-danger' : 'btn-green' }}">{{ $item->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
