@props(['variant' => 'info', 'title' => null])

@php
    $variants = [
        'info' => 'border-primary/15 bg-primary-soft/60 text-primary',
        'success' => 'border-success/15 bg-success-soft/70 text-success',
        'warning' => 'border-warning/15 bg-warning-soft/70 text-warning',
        'danger' => 'border-danger/15 bg-danger-soft/70 text-danger',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border px-4 py-4 text-sm '.($variants[$variant] ?? $variants['info'])]) }}>
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif

    <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
</div>
