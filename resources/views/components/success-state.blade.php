@props(['title' => 'Berhasil', 'description' => 'Perubahan berhasil disimpan.', 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-success/15 bg-white p-6 shadow-sm']) }}>
    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-success-soft text-success">
        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-6 w-6">
            <path d="M16.25 5.75L8.5 13.5L3.75 8.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <h3 class="mt-4 text-lg font-semibold text-ink">{{ $title }}</h3>
    <p class="mt-2 text-sm text-charcoal">{{ $description }}</p>

    @if ($actionLabel)
        <x-button.outline type="button" class="mt-5">{{ $actionLabel }}</x-button.outline>
    @endif
</div>
