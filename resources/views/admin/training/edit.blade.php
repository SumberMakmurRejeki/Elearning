<x-layouts.admin title="Edit Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Edit Training"
            description="Perbarui data training dan pengaturan test tanpa mengubah alur modul lain."
        >
            <x-button.link href="{{ route('admin.training.show', $training) }}" variant="text">Kembali ke Detail</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            @include('admin.training.partials.form', [
                'training' => $training,
                'action' => route('admin.training.update', $training),
                'method' => 'PUT',
                'booleanOptions' => $booleanOptions,
                'cancelHref' => $backRoute,
                'submitLabel' => 'Simpan Perubahan',
                'isEdit' => true,
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
