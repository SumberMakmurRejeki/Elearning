<x-layouts.admin title="Tambah Jabatan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Tambah Jabatan"
            description="Tambahkan jabatan baru untuk digunakan pada data karyawan."
        >
            <x-button.link href="{{ $backRoute }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        <x-card.base>
            @include('admin.jabatan.partials.form', [
                'position' => $position,
                'action' => route('admin.jabatan.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Jabatan',
                'cancelHref' => $backRoute,
                'statusOptions' => $statusOptions,
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
