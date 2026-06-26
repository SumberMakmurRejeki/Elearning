@props([
    'id',
    'title' => 'Konfirmasi aksi',
    'description' => 'Apakah Anda yakin ingin melanjutkan aksi ini?',
    'confirmLabel' => 'Konfirmasi',
    'cancelLabel' => 'Batal',
    'danger' => false,
    'action' => null,
    'method' => 'POST',
    'open' => false,
])

<div
    x-data="{ open: @js($open) }"
    x-cloak
    data-modal-id="{{ $id }}"
    x-on:modal:open.window="if ($event.detail === @js('#' . $id)) open = true"
    x-on:modal:close.window="if (!$event.detail || $event.detail === @js('#' . $id)) open = false"
    x-on:keydown.escape.window="open = false"
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 bg-ink/50"
        aria-hidden="true"
        @click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}-title"
    >
        <div class="w-full max-w-lg rounded-3xl border border-fog bg-white p-6 shadow-2xl" tabindex="-1">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 id="{{ $id }}-title" class="text-lg font-semibold text-ink">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-charcoal">{{ $description }}</p>
                </div>

                <button type="button" class="rounded-lg border border-fog px-3 py-2 text-sm" data-modal-close="#{{ $id }}">✕</button>
            </div>

            @if (! $slot->isEmpty())
                <div class="mt-4">
                    {{ $slot }}
                </div>
            @endif

            @if ($action)
                <form method="POST" action="{{ $action }}" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    @csrf
                    @if (strtoupper($method) !== 'POST')
                        @method(strtoupper($method))
                    @endif

                    <x-button.outline type="button" data-modal-close="#{{ $id }}">{{ $cancelLabel }}</x-button.outline>

                    @if ($danger)
                        <x-button.danger type="submit">{{ $confirmLabel }}</x-button.danger>
                    @else
                        <x-button.primary type="submit">{{ $confirmLabel }}</x-button.primary>
                    @endif
                </form>
            @else
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-button.outline type="button" data-modal-close="#{{ $id }}">{{ $cancelLabel }}</x-button.outline>

                    @if ($danger)
                        <x-button.danger type="button">{{ $confirmLabel }}</x-button.danger>
                    @else
                        <x-button.primary type="button">{{ $confirmLabel }}</x-button.primary>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
