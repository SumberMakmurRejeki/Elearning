@props(['type' => 'button', 'label' => 'Action'])

<button {{ $attributes->merge(['type' => $type, 'class' => 'inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2']) }} aria-label="{{ $label }}" title="{{ $label }}">
    {{ $slot }}
</button>
