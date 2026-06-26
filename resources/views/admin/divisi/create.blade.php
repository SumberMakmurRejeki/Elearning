<x-layouts.admin title="Tambah Divisi - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Tambah Divisi"
            description="Tambahkan divisi baru untuk digunakan pada data karyawan."
        >
            <x-button.link href="{{ $backRoute }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        <x-card.base>
            @include('admin.divisi.partials.form', [
                'division' => $division,
                'action' => route('admin.divisi.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Divisi',
                'cancelHref' => $backRoute,
                'statusOptions' => $statusOptions,
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
