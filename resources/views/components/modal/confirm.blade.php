@props([
    'id',
    'title' => 'Konfirmasi aksi',
    'description' => 'Apakah Anda yakin ingin melanjutkan aksi ini?',
    'confirmLabel' => 'Konfirmasi',
    'cancelLabel' => 'Batal',
    'danger' => false,
])

<dialog id="{{ $id }}" class="w-full max-w-lg rounded-2xl border border-fog bg-white p-0 shadow-lg backdrop:bg-black/40">
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-ink">{{ $title }}</h3>
                <p class="mt-2 text-sm text-charcoal">{{ $description }}</p>
            </div>

            <button type="button" class="rounded-lg border border-fog px-3 py-2 text-sm" data-modal-close>✕</button>
        </div>

        @if (! $slot->isEmpty())
            <div class="mt-4">
                {{ $slot }}
            </div>
        @endif

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-button.outline type="button" data-modal-close>{{ $cancelLabel }}</x-button.outline>

            @if ($danger)
                <x-button.danger type="button">{{ $confirmLabel }}</x-button.danger>
            @else
                <x-button.primary type="button">{{ $confirmLabel }}</x-button.primary>
            @endif
        </div>
    </div>
</dialog>
