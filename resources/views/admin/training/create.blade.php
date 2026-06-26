<x-layouts.admin title="Tambah Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Tambah Training"
            description="Buat data training baru. Training baru akan otomatis disimpan sebagai draft."
        >
            <x-button.link href="{{ route('admin.training.index') }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            @include('admin.training.partials.form', [
                'training' => $training,
                'action' => route('admin.training.store'),
                'booleanOptions' => $booleanOptions,
                'cancelHref' => $backRoute,
                'submitLabel' => 'Simpan Training',
                'isEdit' => false,
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
