@props([
    'label' => null,
    'name',
    'id' => null,
    'options' => [],
    'placeholder' => 'Pilih opsi',
    'error' => null,
])

@php
    $fieldId = $id ?? $name;
    $fieldError = $error ?? $errors->first($name);
    $selected = old($name, $attributes->get('value'));
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
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>

    @if ($fieldError)
        <p class="text-xs font-medium text-danger">{{ $fieldError }}</p>
    @endif
</div>
