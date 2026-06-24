@props(['title' => 'Terjadi kesalahan', 'description' => 'Silakan coba lagi atau hubungi admin.', 'actionLabel' => 'Coba Lagi'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-danger/20 bg-white p-6 text-center shadow-sm']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl text-danger">!</div>
    <h3 class="mt-4 text-lg font-semibold text-ink">{{ $title }}</h3>
    <p class="mt-2 text-sm text-charcoal">{{ $description }}</p>
    @if ($actionLabel)
        <x-button.outline type="button" class="mt-5">{{ $actionLabel }}</x-button.outline>
    @endif
</div>
