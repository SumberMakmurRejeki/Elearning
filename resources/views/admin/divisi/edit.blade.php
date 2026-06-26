<x-layouts.admin title="Edit Divisi - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Edit Divisi"
            description="Perbarui informasi divisi yang sudah ada."
        >
            <x-button.link href="{{ route('admin.divisi.show', $division) }}" variant="text">Lihat Detail</x-button.link>
            <x-button.link href="{{ $backRoute }}" variant="primary">Kembali</x-button.link>
        </x-page.header>

        <x-card.base>
            @include('admin.divisi.partials.form', [
                'division' => $division,
                'action' => route('admin.divisi.update', $division),
                'method' => 'PUT',
                'submitLabel' => 'Simpan Perubahan',
                'cancelHref' => $backRoute,
                'statusOptions' => $statusOptions,
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
