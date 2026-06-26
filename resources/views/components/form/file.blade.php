@props([
    'label' => null,
    'name',
    'id' => null,
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

    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="file"
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-ink file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
            'border-danger' => $fieldError,
            'border-fog' => ! $fieldError,
        ]) }}
    >

    @if ($help && ! $fieldError)
        <p class="text-xs text-graphite">{{ $help }}</p>
    @endif

    @if ($fieldError)
        <p class="text-xs font-medium text-danger">{{ $fieldError }}</p>
    @endif
</div>
