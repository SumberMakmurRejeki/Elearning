@php
    $layout = match ($role) {
        'admin' => 'layouts.admin',
        'employee' => 'layouts.employee',
        default => 'layouts.guest',
    };

    $backRoute = match ($role) {
        'admin' => route('admin.dashboard'),
        'employee' => route('employee.dashboard'),
        default => route('preview.login'),
    };
@endphp

<x-dynamic-component :component="$layout" :title="$title . ' - ' . config('app.name')">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Placeholder Routing"
            :title="$title"
            :description="$description"
        >
            <a href="{{ $backRoute }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                Kembali ke Dashboard
            </a>
            <x-button.primary type="button">Aksi Dummy</x-button.primary>
        </x-page.header>

        <x-card.base>
            <div class="space-y-3">
                <x-badge variant="info">EPIC-03 Routing Awal</x-badge>
                <p class="text-sm leading-6 text-charcoal">
                    Halaman ini adalah placeholder untuk jalur navigasi awal. Konten fitur nyata akan diisi pada epic berikutnya.
                </p>
                <x-empty-state
                    title="Belum ada konten fitur"
                    description="Gunakan halaman ini untuk memastikan route, layout, dan navigasi sudah tersambung dengan benar."
                />
            </div>
        </x-card.base>
    </div>
</x-dynamic-component>
