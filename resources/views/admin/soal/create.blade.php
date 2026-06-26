<x-layouts.admin title="Tambah Soal - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header eyebrow="Master Training" title="Tambah Soal" description="Tambahkan soal pre-test atau post-test dengan tipe pilihan ganda atau essay.">
            <x-button.link href="{{ route('admin.soal.index') }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            @include('admin.soal.partials.form', [
                'question' => $question,
                'action' => route('admin.soal.store'),
                'trainingOptions' => $trainingOptions,
                'testTypeOptions' => $testTypeOptions,
                'questionTypeOptions' => $questionTypeOptions,
                'booleanOptions' => $booleanOptions,
                'cancelHref' => $backRoute,
                'submitLabel' => 'Simpan Soal',
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
