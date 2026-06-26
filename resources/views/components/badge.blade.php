@props(['variant' => 'neutral'])

@php
    $variants = [
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-primary',
        'ink' => 'bg-ink text-white',
        'neutral' => 'bg-fog text-charcoal',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.($variants[$variant] ?? $variants['neutral'])]) }}>
    {{ $slot }}
</span>
