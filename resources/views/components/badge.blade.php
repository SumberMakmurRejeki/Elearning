@props(['variant' => 'neutral'])

@php
    $variants = [
        'success' => 'bg-green-100 text-success',
        'warning' => 'bg-amber-100 text-warning',
        'danger' => 'bg-red-100 text-danger',
        'info' => 'bg-primary-soft text-primary',
        'ink' => 'bg-ink text-white',
        'neutral' => 'bg-fog text-charcoal',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.($variants[$variant] ?? $variants['neutral'])]) }}>
    {{ $slot }}
</span>
