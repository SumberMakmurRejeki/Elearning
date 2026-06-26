<x-layouts.admin title="Edit Soal - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header eyebrow="Master Training" title="Edit Soal" description="Perbarui pertanyaan, bobot, jenis soal, dan opsi jawaban selama soal belum digunakan dalam test.">
            <x-button.link href="{{ route('admin.soal.show', $question) }}" variant="text">Kembali ke Detail</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        @if ($usedInTest)
            <x-alert variant="warning" title="Soal sudah digunakan">
                Soal ini sudah digunakan dalam test. Perubahan struktur soal diblokir untuk menjaga integritas data jawaban.
            </x-alert>
        @endif

        <x-card.base>
            @include('admin.soal.partials.form', [
                'question' => $question,
                'action' => route('admin.soal.update', $question),
                'method' => 'PUT',
                'trainingOptions' => $trainingOptions,
                'testTypeOptions' => $testTypeOptions,
                'questionTypeOptions' => $questionTypeOptions,
                'booleanOptions' => $booleanOptions,
                'cancelHref' => $backRoute,
                'submitLabel' => 'Simpan Perubahan',
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
