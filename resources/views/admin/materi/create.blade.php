<x-layouts.admin title="Tambah Materi - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Tambah Materi"
            description="Tambahkan materi file atau link untuk training tertentu dengan penyimpanan file private."
        >
            <x-button.link href="{{ route('admin.materi.index') }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            @include('admin.materi.partials.form', [
                'material' => $material,
                'action' => route('admin.materi.store'),
                'trainingOptions' => $trainingOptions,
                'materialTypeOptions' => $materialTypeOptions,
                'booleanOptions' => $booleanOptions,
                'cancelHref' => $backRoute,
                'submitLabel' => 'Simpan Materi',
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
