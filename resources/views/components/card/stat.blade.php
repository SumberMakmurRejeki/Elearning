@props(['label', 'value'])

<x-card.base {{ $attributes }}>
    <p class="text-sm font-medium text-graphite">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold text-ink">{{ $value }}</p>
    @if (! $slot->isEmpty())
        <div class="mt-3 text-sm text-charcoal">{{ $slot }}</div>
    @endif
</x-card.base>
