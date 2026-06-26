@props(['title' => 'Belum ada data', 'description' => 'Data akan tampil di sini setelah tersedia.', 'actionLabel' => null, 'actionHref' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-fog bg-cloud/70 px-6 py-12 text-center']) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white text-primary shadow-sm">
        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-7 w-7">
            <path d="M3 7.5L10 3l7 4.5V15a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 15V7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            <path d="M3 7.5L10 12l7-4.5" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
        </svg>
    </div>
    <h3 class="mt-4 text-lg font-semibold text-ink">{{ $title }}</h3>
    <p class="mt-2 max-w-md text-sm text-charcoal">{{ $description }}</p>

    @if ($actionLabel)
        @if ($actionHref)
            <a href="{{ $actionHref }}" class="mt-5 inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-bright focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">{{ $actionLabel }}</a>
        @else
            <x-button.primary type="button" class="mt-5">{{ $actionLabel }}</x-button.primary>
        @endif
    @endif
</div>
