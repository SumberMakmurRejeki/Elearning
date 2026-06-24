@props([
    'label' => null,
    'name',
    'id' => null,
    'rows' => 4,
    'help' => null,
    'error' => null,
])

@php
    $fieldId = $id ?? $name;
    $fieldError = $error ?? $errors->first($name);
@endphp

<div class="space-y-2">
    @if ($label)
        <label for="{{ $fieldId }}" class="block text-sm font-medium text-ink">{{ $label }}</label>
    @endif

    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-ink placeholder:text-graphite focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
            'border-danger' => $fieldError,
            'border-fog' => ! $fieldError,
        ]) }}
    >{{ old($name, $slot->isEmpty() ? $attributes->get('value') : $slot) }}</textarea>

    @if ($help && ! $fieldError)
        <p class="text-xs text-graphite">{{ $help }}</p>
    @endif

    @if ($fieldError)
        <p class="text-xs font-medium text-danger">{{ $fieldError }}</p>
    @endif
</div>
