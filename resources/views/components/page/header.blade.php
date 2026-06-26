@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<section class="flex flex-col gap-4 rounded-3xl border border-fog bg-white p-5 shadow-sm md:flex-row md:items-end md:justify-between md:p-6">
    <div class="space-y-2">
        @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-graphite">{{ $eyebrow }}</p>
        @endif

        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight text-ink md:text-3xl">{{ $title }}</h1>
            @if ($description)
                <p class="max-w-3xl text-sm leading-6 text-charcoal md:text-base">{{ $description }}</p>
            @endif
        </div>
    </div>

    @if (! $slot->isEmpty())
        <div class="flex flex-wrap gap-2">
            {{ $slot }}
        </div>
    @endif
</section>
