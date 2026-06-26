<x-layouts.admin title="Detail Penugasan Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header eyebrow="Master Training" title="Detail Penugasan Training" description="Lihat ringkasan training, karyawan, dan status progress dasar dari penugasan ini.">
            <x-button.link href="{{ route('admin.penugasan.index') }}" variant="text">Kembali</x-button.link>
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
                        <p class="text-sm font-medium text-graphite">Training</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $progress->training?->title ?? '-' }}</h2>
                    </div>

                    <x-badge :variant="$progress->status === 'not_started' ? 'neutral' : 'info'">{{ $statusLabel }}</x-badge>
                </div>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Karyawan</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $progress->employee?->user?->name ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Divisi</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $progress->employee?->division?->name ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jabatan</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $progress->employee?->position?->name ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Ditugaskan</dt><dd class="mt-2 text-sm font-medium text-ink">{{ optional($progress->assignment?->assigned_at)->translatedFormat('d M Y') ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Mulai</dt><dd class="mt-2 text-sm font-medium text-ink">{{ optional($progress->pre_test_completed_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Selesai</dt><dd class="mt-2 text-sm font-medium text-ink">{{ optional($progress->completed_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Kelulusan</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $progress->final_status ? ucfirst(str_replace('_', ' ', $progress->final_status)) : '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Catatan Sistem</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $canCancel ? 'Penugasan masih dapat dibatalkan.' : 'Penugasan sudah berjalan dan tidak dapat dibatalkan.' }}</dd></div>
                </dl>
            </x-card.base>

            <div class="space-y-6">
                @if (! $canCancel)
                    <x-alert variant="warning" title="Penugasan sudah berjalan">
                        Penugasan tidak dapat dibatalkan karena karyawan sudah memulai training.
                    </x-alert>
                @endif

                <x-card.base>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Aksi Cepat</h3>
                        <p class="text-sm text-charcoal">Batalkan penugasan hanya jika progress masih pada status awal dan belum ada aktivitas training.</p>

                        @if ($canCancel)
                            <button type="button" class="inline-flex w-full items-center justify-center rounded-lg border border-danger bg-white px-4 py-2.5 text-sm font-semibold text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#cancel-progress-{{ $progress->id }}">Batalkan Penugasan</button>
                        @endif
                    </div>
                </x-card.base>
            </div>
        </div>

        @if ($canCancel)
            <x-modal.confirm id="cancel-progress-{{ $progress->id }}" title="Batalkan Penugasan Training" description="Penugasan yang dibatalkan akan menghapus progress awal karyawan untuk training ini." confirm-label="Batalkan Penugasan" :danger="true" :action="route('admin.penugasan.destroy', $progress)" method="DELETE" />
        @endif
    </div>
</x-layouts.admin>
