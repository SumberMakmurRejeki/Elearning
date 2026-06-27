<x-layouts.admin title="Detail Progress Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Monitoring & Laporan"
            title="Detail Progress Training"
            description="Informasi lengkap progress training untuk karyawan tertentu."
        >
            <x-button.link href="{{ route('admin.progress.index') }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
            <x-card.base class="space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-graphite">Training</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $progress->training?->title ?? '-' }}</h2>
                    </div>
                    <x-badge :variant="match ($progress->status) { 'not_started' => 'neutral', 'passed' => 'success', 'failed' => 'danger', 'waiting_essay_review' => 'warning', default => 'info' }">
                        {{ $statusLabel }}
                    </x-badge>
                </div>

                <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Karyawan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $progress->employee?->user?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Divisi</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $progress->employee?->division?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jabatan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $progress->employee?->position?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Ditugaskan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($progress->assignment?->assigned_at)->translatedFormat('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Mulai</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($progress->pre_test_completed_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Selesai</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($progress->completed_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Passing Grade</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $progress->training?->passing_grade !== null ? number_format((float) $progress->training->passing_grade, 0) : '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Nilai Akhir</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $progress->final_score !== null ? number_format((float) $progress->final_score, 2) : 'Belum tersedia' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Kelulusan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $finalStatusLabel ?? 'Belum tersedia' }}</dd>
                    </div>
                </dl>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-ink">Langkah Training</h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-fog bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-base font-semibold text-ink">Pre-Test</h4>
                                <x-badge :variant="$progress->pre_test_completed_at !== null ? 'success' : 'neutral'">
                                    {{ $progress->pre_test_completed_at !== null ? 'Selesai' : 'Belum Dikerjakan' }}
                                </x-badge>
                            </div>
                            <p class="mt-3 text-sm text-charcoal">
                                @if ($progress->pre_test_completed_at !== null)
                                    Dikerjakan pada {{ $progress->pre_test_completed_at->translatedFormat('d M Y, H:i') }}.
                                @else
                                    Belum dikerjakan oleh karyawan.
                                @endif
                            </p>
                        </div>
                        <div class="rounded-2xl border border-fog bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-base font-semibold text-ink">Materi</h4>
                                <x-badge :variant="$openedMaterials >= $activeMaterials ? 'success' : ($openedMaterials > 0 ? 'info' : 'neutral')">
                                    {{ $openedMaterials }} / {{ $activeMaterials }} dibuka
                                </x-badge>
                            </div>
                            <p class="mt-3 text-sm text-charcoal">
                                @if ($activeMaterials === 0)
                                    Tidak ada materi aktif untuk training ini.
                                @elseif ($openedMaterials >= $activeMaterials)
                                    Semua materi sudah dibuka.
                                @else
                                    Masih ada materi yang belum dibuka.
                                @endif
                            </p>
                        </div>
                        <div class="rounded-2xl border border-fog bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-base font-semibold text-ink">Post-Test</h4>
                                <x-badge :variant="$progress->post_test_completed_at !== null ? 'success' : 'neutral'">
                                    {{ $progress->post_test_completed_at !== null ? 'Selesai' : 'Belum Dikerjakan' }}
                                </x-badge>
                            </div>
                            <p class="mt-3 text-sm text-charcoal">
                                @if ($progress->post_test_completed_at !== null)
                                    Dikerjakan pada {{ $progress->post_test_completed_at->translatedFormat('d M Y, H:i') }}.
                                @else
                                    Belum dikerjakan oleh karyawan.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if ($attempts->isNotEmpty())
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Riwayat Attempt Test</h3>
                        <div class="overflow-hidden rounded-2xl border border-fog">
                            <table class="min-w-full divide-y divide-fog text-left text-sm text-charcoal">
                                <thead class="bg-cloud/80 text-xs font-semibold uppercase tracking-[0.14em] text-graphite">
                                    <tr>
                                        <th class="px-6 py-4">Attempt #</th>
                                        <th class="px-6 py-4">Jenis Test</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Skor MCQ</th>
                                        <th class="px-6 py-4">Skor Essay</th>
                                        <th class="px-6 py-4">Nilai Akhir</th>
                                        <th class="px-6 py-4">Status Kelulusan</th>
                                        <th class="px-6 py-4">Tanggal Submit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attempts as $attempt)
                                        <tr class="border-b border-fog last:border-b-0">
                                            <td class="px-6 py-4">{{ $attempt->attempt_number }}</td>
                                            <td class="px-6 py-4">{{ $attempt->test_type === 'pre_test' ? 'Pre-Test' : 'Post-Test' }}</td>
                                            <td class="px-6 py-4">
                                                <x-badge :variant="match ($attempt->status) { 'completed' => 'success', 'in_progress' => 'info', default => 'neutral' }">
                                                    {{ ucfirst(str_replace('_', ' ', $attempt->status)) }}
                                                </x-badge>
                                            </td>
                                            <td class="px-6 py-4">{{ number_format((float) $attempt->mcq_score, 2) }}</td>
                                            <td class="px-6 py-4">{{ number_format((float) $attempt->essay_score, 2) }}</td>
                                            <td class="px-6 py-4">{{ $attempt->final_score !== null ? number_format((float) $attempt->final_score, 2) : '-' }}</td>
                                            <td class="px-6 py-4">
                                                @if ($attempt->pass_status === 'passed')
                                                    <x-badge variant="success">Lulus</x-badge>
                                                @elseif ($attempt->pass_status === 'failed')
                                                    <x-badge variant="danger">Tidak Lulus</x-badge>
                                                @else
                                                    <span class="text-xs text-graphite">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">{{ optional($attempt->submitted_at)->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </x-card.base>

            <div class="space-y-6">
                <x-card.base class="space-y-4">
                    <h3 class="text-lg font-semibold text-ink">Catatan Sistem</h3>
                    <p class="text-sm text-charcoal">Halaman ini hanya untuk melihat data progress. Untuk mengubah data, silakan buka halaman Penugasan Training atau Penilaian Essay.</p>
                </x-card.base>
            </div>
        </div>
    </div>
</x-layouts.admin>
