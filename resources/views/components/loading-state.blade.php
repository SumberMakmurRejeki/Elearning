@props(['lines' => 4])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-fog bg-white p-6 shadow-sm']) }}>
    <div class="animate-pulse space-y-4">
        <div class="h-5 w-40 rounded bg-fog"></div>
        @for ($i = 0; $i < $lines; $i++)
            <div class="h-4 rounded bg-cloud"></div>
        @endfor
    </div>
</div>
