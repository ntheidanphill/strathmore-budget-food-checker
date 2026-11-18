{{-- STUDENT SEARCH PAGE (R1) - the front door of the app. No login needed.
     Receives: $locations, $categories and $items (null until the student searches). --}}
@extends('layouts.app')
@section('title', 'Find Food Within Your Budget')

@section('content')

{{-- ============ HERO + SEARCH FORM ============ --}}
<section class="hero">
    {{-- photo collage background: real campus spots (images in public/images) --}}
    <div class="hero-collage" aria-hidden="true">
        <div class="shot shot-sc"><span class="shot-label">📍 Student Centre</span></div>
        <div class="shot shot-sw"><span class="shot-label">📍 Siwaka</span></div>
    </div>

    {{-- decorative floating food emojis (pure CSS animation) --}}
    <span class="float f1">🍛</span><span class="float f2">🥟</span>
    <span class="float f3">🍟</span><span class="float f4">🥤</span>

    <h1>How much can you spend today? 💸</h1>
    <p>Type your budget and we'll show every meal you can afford around Strathmore — cheapest first.</p>

    {{-- GET form: the search shows in the URL so students can share it --}}
    <form method="GET" action="{{ route('search') }}" class="search-card">
        <div class="budget-row">
            <div class="money">
                <span>KES</span>
                <input type="number" id="budget" name="budget" value="{{ request('budget') }}"
                       placeholder="200" min="1" max="100000" step="1" inputmode="numeric" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Find food 🍽️</button>
        </div>
        @error('budget') <p class="field-error">{{ $message }}</p> @enderror

        {{-- Quick budget chips: app.js fills the box and submits --}}
        <div class="chips">
            @foreach ([50, 100, 150, 200, 300, 500] as $amount)
                <button type="button" class="chip" data-budget="{{ $amount }}">KES {{ $amount }}</button>
            @endforeach
        </div>

        {{-- Location pills (radio buttons styled with CSS) --}}
        <div class="pills">
            <label class="pill">
                <input type="radio" name="location" value="" @checked(! request()->filled('location'))>
                <span>📍 All locations</span>
            </label>
            @foreach ($locations as $location)
                <label class="pill">
                    <input type="radio" name="location" value="{{ $location->id }}" @checked((string) request('location') === (string) $location->id)>
                    <span>{{ $location->name }}</span>
                </label>
            @endforeach
        </div>

        <label style="margin:0">Food type (optional)
            <select name="category">
                <option value="">Any food type</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->emoji }} {{ $category->name }}</option>
                @endforeach
            </select>
        </label>
    </form>
</section>

{{-- ============ RESULTS ============ --}}
@if ($items !== null)
    <div class="results-head">
        <h2>{{ $items->count() }} {{ \Illuminate\Support\Str::plural('meal', $items->count()) }} within KES {{ number_format((float) request('budget'), 0) }}</h2>
        <span class="muted">Cheapest first</span>
    </div>

    @if ($items->isEmpty())
        <div class="empty">
            <div class="big">😢</div>
            <h3>Nothing that cheap right now</h3>
            <p class="muted">Try a slightly higher budget, a different location, or "Any food type".</p>
        </div>
    @else
        <div class="grid">
            @foreach ($items as $item)
                {{-- --i delays each card's entrance animation so they appear one by one --}}
                <article class="card" style="--i: {{ $loop->index }}">
                    <span class="emoji">{{ $item->category->emoji }}</span>
                    <span class="price">KES {{ number_format($item->price, 0) }}</span>
                    <h3>{{ $item->name }}</h3>
                    <div>🏪 {{ $item->vendor->name }}</div>
                    <span class="tag">📍 {{ $item->vendor->location->name }}</span>
                    <span class="tag">{{ $item->category->name }}</span>
                    <div class="change">You keep KES {{ number_format((float) request('budget') - $item->price, 0) }}</div>
                </article>
            @endforeach
        </div>
    @endif
@else
    <div class="empty" style="margin-top:1.5rem">
        <div class="big">👆</div>
        <p class="muted">Enter your budget above (or tap a quick amount) to see what's on the menu.</p>
    </div>
@endif

@endsection
