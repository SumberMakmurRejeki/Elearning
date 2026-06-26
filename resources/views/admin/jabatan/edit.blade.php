<x-layouts.admin title="Edit Jabatan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Edit Jabatan"
            description="Perbarui informasi jabatan yang sudah ada."
        >
            <x-button.link href="{{ route('admin.jabatan.show', $position) }}" variant="text">Lihat Detail</x-button.link>
            <x-button.link href="{{ $backRoute }}" variant="primary">Kembali</x-button.link>
        </x-page.header>

        <x-card.base>
            @include('admin.jabatan.partials.form', [
                'position' => $position,
                'action' => route('admin.jabatan.update', $position),
                'method' => 'PUT',
                'submitLabel' => 'Simpan Perubahan',
                'cancelHref' => $backRoute,
                'statusOptions' => $statusOptions,
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
