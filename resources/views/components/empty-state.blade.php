@props(['title' => 'Belum ada data', 'description' => 'Data akan tampil di sini setelah tersedia.', 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-fog bg-cloud/70 px-6 py-12 text-center']) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white text-2xl shadow-sm">📭</div>
    <h3 class="mt-4 text-lg font-semibold text-ink">{{ $title }}</h3>
    <p class="mt-2 max-w-md text-sm text-charcoal">{{ $description }}</p>

    @if ($actionLabel)
        <x-button.primary type="button" class="mt-5">{{ $actionLabel }}</x-button.primary>
    @endif
</div>
