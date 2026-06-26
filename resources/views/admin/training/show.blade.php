<x-layouts.admin title="Detail Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Detail Training"
            description="Lihat ringkasan training, pengaturan test, serta data terkait sebelum publish, archive, atau delete permanen."
        >
            <x-button.link href="{{ route('admin.training.index') }}" variant="text">Kembali</x-button.link>
            <x-button.link href="{{ route('admin.training.edit', $training) }}" variant="primary">Edit Training</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
            <x-card.base>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-graphite">Judul Training</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $training->title }}</h2>
                    </div>

                    <x-badge :variant="match ($training->status) { 'published' => 'success', 'archived' => 'neutral', default => 'warning' }">{{ ucfirst($training->status) }}</x-badge>
                </div>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Periode</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($training->start_date)->translatedFormat('d M Y') }} - {{ optional($training->end_date)->translatedFormat('d M Y') }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Materi Training</dt>
                        <dd class="mt-2 text-2xl font-semibold text-ink">{{ $materialCount }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Soal Test</dt>
                        <dd class="mt-2 text-2xl font-semibold text-ink">{{ $questionCount }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Penugasan Training</dt>
                        <dd class="mt-2 text-2xl font-semibold text-ink">{{ $assignmentCount }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Progress Karyawan</dt>
                        <dd class="mt-2 text-2xl font-semibold text-ink">{{ $progressCount }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Attempt Test</dt>
                        <dd class="mt-2 text-2xl font-semibold text-ink">{{ $attemptCount }}</dd>
                    </div>
                </dl>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-2xl border border-fog bg-white p-4">
                        <p class="text-sm font-semibold text-ink">Deskripsi Training</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-charcoal">{{ $training->description ?: 'Tidak ada deskripsi training.' }}</p>
                    </div>

                    <div class="rounded-2xl border border-fog bg-white p-4">
                        <p class="text-sm font-semibold text-ink">Pengaturan Test</p>
                        <ul class="mt-3 space-y-2 text-sm text-charcoal">
                            <li>Pre-Test: {{ $training->has_pre_test ? 'Ya' : 'Tidak' }}</li>
                            <li>Post-Test: {{ $training->has_post_test ? 'Ya' : 'Tidak' }}</li>
                            <li>Passing Grade: {{ $training->passing_grade !== null ? number_format((float) $training->passing_grade, 0) : '-' }}</li>
                            <li>Pengulangan Post-Test: {{ $training->allow_post_test_retake ? 'Diizinkan' : 'Tidak diizinkan' }}</li>
                            <li>Maksimal Percobaan: {{ $training->max_post_test_attempt ?? '-' }}</li>
                            <li>Tampilkan Nilai ke Karyawan: {{ $training->show_score_to_employee ? 'Ya' : 'Tidak' }}</li>
                        </ul>
                    </div>
                </div>
            </x-card.base>

            <div class="space-y-6">
                @if ($hasDependencies)
                    <x-alert variant="warning" title="Training sedang digunakan">
                        Training ini sudah memiliki data terkait. Delete permanen akan ditolak oleh sistem.
                    </x-alert>
                @endif

                <x-card.base>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Aksi Cepat</h3>
                        <p class="text-sm text-charcoal">Publish training draft, archive training published, atau hapus permanen jika belum memiliki data terkait.</p>

                        <div class="flex flex-col gap-3">
                            @if ($training->status === 'draft')
                                <button type="button" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#publish-training-{{ $training->id }}">Publish Training</button>
                            @elseif ($training->status === 'published')
                                <button type="button" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#archive-training-{{ $training->id }}">Archive Training</button>
                            @endif

                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-danger bg-white px-4 py-2.5 text-sm font-semibold text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-training-{{ $training->id }}">Delete Permanen</button>
                        </div>
                    </div>
                </x-card.base>
            </div>
        </div>

        @if ($training->status === 'draft')
            <x-modal.confirm
                id="publish-training-{{ $training->id }}"
                title="Publish Training"
                description="Training draft ini akan dipublish dan siap dipakai untuk penugasan."
                confirm-label="Publish"
                :action="route('admin.training.status', $training)"
                method="PATCH"
            >
                <input type="hidden" name="status" value="published">
            </x-modal.confirm>
        @elseif ($training->status === 'published')
            <x-modal.confirm
                id="archive-training-{{ $training->id }}"
                title="Archive Training"
                description="Training published ini akan diarsipkan dan tidak tersedia untuk penugasan baru."
                confirm-label="Archive"
                :action="route('admin.training.status', $training)"
                method="PATCH"
            >
                <input type="hidden" name="status" value="archived">
            </x-modal.confirm>
        @endif

        <x-modal.confirm
            id="delete-training-{{ $training->id }}"
            title="Delete Permanen Training"
            description="Training yang dihapus permanen tidak dapat dikembalikan."
            confirm-label="Delete Permanen"
            :danger="true"
            :action="route('admin.training.destroy', $training)"
            method="DELETE"
        />
    </div>
</x-layouts.admin>
