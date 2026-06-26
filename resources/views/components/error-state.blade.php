@props(['title' => 'Terjadi kesalahan', 'description' => 'Silakan coba lagi atau hubungi admin.', 'actionLabel' => 'Coba Lagi'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-danger/20 bg-white p-6 text-center shadow-sm']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-danger-soft text-danger">
        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-7 w-7">
            <path d="M10 6.5v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M10 13.5h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M10 3.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13Z" stroke="currentColor" stroke-width="1.5"/>
        </svg>
    </div>
    <h3 class="mt-4 text-lg font-semibold text-ink">{{ $title }}</h3>
    <p class="mt-2 text-sm text-charcoal">{{ $description }}</p>
    @if ($actionLabel)
        <x-button.outline type="button" class="mt-5">{{ $actionLabel }}</x-button.outline>
    @endif
</div>
