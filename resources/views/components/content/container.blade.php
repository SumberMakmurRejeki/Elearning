@props(['maxWidth' => 'max-w-7xl'])

<div {{ $attributes->merge(['class' => 'mx-auto w-full '.$maxWidth]) }}>
    {{ $slot }}
</div>
