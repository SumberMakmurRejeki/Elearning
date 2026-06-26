<x-layouts.admin title="Edit Materi - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Edit Materi"
            description="Perbarui sumber materi, file/link, urutan, dan status aktif materi training."
        >
            <x-button.link href="{{ route('admin.materi.show', $material) }}" variant="text">Kembali ke Detail</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            @include('admin.materi.partials.form', [
                'material' => $material,
                'action' => route('admin.materi.update', $material),
                'method' => 'PUT',
                'trainingOptions' => $trainingOptions,
                'materialTypeOptions' => $materialTypeOptions,
                'booleanOptions' => $booleanOptions,
                'cancelHref' => $backRoute,
                'submitLabel' => 'Simpan Perubahan',
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
