@props([
    'variant' => 'text',
])

@php
    $base = 'inline-flex items-center justify-center rounded-lg text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    $styles = match ($variant) {
        'primary' => 'bg-primary text-white hover:bg-primary-bright focus:ring-primary',
        'icon' => 'h-10 w-10 border border-fog bg-white px-0 text-ink hover:border-primary hover:text-primary focus:ring-primary',
        'danger' => 'border border-danger bg-white text-danger hover:bg-danger-soft focus:ring-danger',
        default => 'border border-fog bg-white px-4 py-2.5 text-ink hover:border-primary hover:text-primary focus:ring-primary',
    };
@endphp

<a {{ $attributes->class([$base, $styles]) }}>
    {{ $slot }}
</a>
