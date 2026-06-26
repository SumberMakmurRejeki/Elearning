@props(['variant' => 'success', 'title' => 'Tersimpan', 'description' => null])

@php
    $variants = [
        'success' => 'border-success/15 bg-white text-success',
        'info' => 'border-primary/15 bg-white text-primary',
        'warning' => 'border-warning/15 bg-white text-warning',
        'danger' => 'border-danger/15 bg-white text-danger',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border px-4 py-4 shadow-lg '.($variants[$variant] ?? $variants['success'])]) }}>
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-full bg-current/10">
            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-5 w-5">
                <path d="M16.25 5.75L8.5 13.5L3.75 8.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <div>
            <p class="font-semibold">{{ $title }}</p>
            @if ($description)
                <p class="mt-1 text-sm text-charcoal">{{ $description }}</p>
            @endif
        </div>
    </div>
</div>
