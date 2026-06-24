<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-fog bg-white shadow-sm']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-fog text-left text-sm text-charcoal">
            {{ $slot }}
        </table>
    </div>
</div>
