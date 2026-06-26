@props([
    'label' => null,
    'name',
    'id' => null,
    'help' => null,
    'error' => null,
    'options' => [],
    'placeholder' => null,
    'selected' => null,
])

@php
    $fieldId = $id ?? $name;
    $fieldError = $error ?? $errors->first($name);
    $fieldValue = old($name, $selected);
@endphp

<div class="space-y-2">
    @if ($label)
        <label for="{{ $fieldId }}" class="block text-sm font-medium text-ink">{{ $label }}</label>
    @endif

    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
            'border-danger' => $fieldError,
            'border-fog' => ! $fieldError,
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) $fieldValue !== '' && (string) $fieldValue === (string) $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($help && ! $fieldError)
        <p class="text-xs text-graphite">{{ $help }}</p>
    @endif

    @if ($fieldError)
        <p class="text-xs font-medium text-danger">{{ $fieldError }}</p>
    @endif
</div>
