@props([
    'training',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
    'cancelHref',
    'booleanOptions',
    'isEdit' => false,
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf

    @if (strtoupper($method) !== 'POST')
        @method(strtoupper($method))
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.input
            label="Judul Training"
            name="title"
            placeholder="Contoh: Training K3 Dasar"
            :value="old('title', $training->title)"
        />

        <div class="rounded-2xl border border-fog bg-cloud/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Saat Ini</p>
            <div class="mt-2 flex items-center gap-3">
                <x-badge :variant="match ($training->status) { 'published' => 'success', 'archived' => 'neutral', default => 'warning' }">
                    {{ $training->status ? ucfirst($training->status) : 'Draft' }}
                </x-badge>
                <p class="text-sm text-charcoal">{{ $isEdit ? 'Ubah status lewat halaman detail training.' : 'Training baru akan otomatis disimpan sebagai draft.' }}</p>
            </div>
        </div>
    </div>

    <x-form.textarea
        label="Deskripsi Training"
        name="description"
        rows="5"
        placeholder="Opsional: jelaskan tujuan dan cakupan training"
    >{{ old('description', $training->description) }}</x-form.textarea>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.input
            label="Tanggal Mulai"
            name="start_date"
            type="date"
            :value="old('start_date', optional($training->start_date)->format('Y-m-d') ?? $training->start_date)"
        />

        <x-form.input
            label="Tanggal Selesai"
            name="end_date"
            type="date"
            :value="old('end_date', optional($training->end_date)->format('Y-m-d') ?? $training->end_date)"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.select
            label="Menggunakan Pre-Test"
            name="has_pre_test"
            :options="$booleanOptions"
            :selected="old('has_pre_test', (string) ((bool) $training->has_pre_test ? 1 : 0))"
        />

        <x-form.select
            label="Menggunakan Post-Test"
            name="has_post_test"
            :options="$booleanOptions"
            :selected="old('has_post_test', (string) ((bool) $training->has_post_test ? 1 : 0))"
        />

        <x-form.input
            label="Passing Grade"
            name="passing_grade"
            type="number"
            min="0"
            max="100"
            step="0.01"
            placeholder="Contoh: 75"
            :value="old('passing_grade', $training->passing_grade)"
            help="Wajib diisi jika post-test aktif."
        />

        <x-form.select
            label="Izinkan Pengulangan Post-Test"
            name="allow_post_test_retake"
            :options="$booleanOptions"
            :selected="old('allow_post_test_retake', (string) ((bool) $training->allow_post_test_retake ? 1 : 0))"
        />

        <x-form.input
            label="Jumlah Maksimal Percobaan"
            name="max_post_test_attempt"
            type="number"
            min="1"
            step="1"
            placeholder="Contoh: 3"
            :value="old('max_post_test_attempt', $training->max_post_test_attempt)"
            help="Wajib diisi jika pengulangan post-test diizinkan."
        />

        <x-form.select
            label="Tampilkan Nilai ke Karyawan"
            name="show_score_to_employee"
            :options="$booleanOptions"
            :selected="old('show_score_to_employee', (string) ((bool) $training->show_score_to_employee ? 1 : 0))"
        />
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $cancelHref }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Batal</a>
        <x-button.primary type="submit">{{ $submitLabel }}</x-button.primary>
    </div>
</form>
