@php
    $menu = [
        'Dashboard' => [
            ['label' => 'Dashboard Admin', 'route' => 'admin.dashboard'],
        ],
        'Master Data' => [
            ['label' => 'Karyawan', 'route' => 'admin.karyawan.index'],
            ['label' => 'Divisi', 'route' => 'admin.divisi.index'],
            ['label' => 'Jabatan', 'route' => 'admin.jabatan.index'],
        ],
        'Master Training' => [
            ['label' => 'Daftar Training', 'route' => 'admin.training.index'],
            ['label' => 'Materi Training', 'route' => 'admin.materi.index'],
            ['label' => 'Soal Test', 'route' => 'admin.soal.index'],
            ['label' => 'Penugasan Training', 'route' => 'admin.penugasan.index'],
        ],
        'Penilaian' => [
            ['label' => 'Jawaban Essay', 'route' => 'admin.essay-answers.index'],
            ['label' => 'Hasil Test', 'route' => 'admin.hasil-test.index'],
        ],
        'Monitoring & Laporan' => [
            ['label' => 'Progress Training', 'route' => 'admin.progress.index'],
            ['label' => 'Laporan', 'route' => 'admin.laporan.index'],
            ['label' => 'Export Data', 'route' => 'admin.export.index'],
        ],
        'Pengaturan User' => [
            ['label' => 'Profil Admin', 'route' => 'admin.profile.index'],
            ['label' => 'Ubah Password', 'route' => 'admin.password.index'],
        ],
    ];
@endphp

<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-fog bg-white transition-transform duration-200 lg:translate-x-0">
    <div class="flex items-center justify-between border-b border-fog px-6 py-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-graphite">Admin Panel</p>
            <h1 class="text-lg font-semibold text-ink">{{ config('app.name') }}</h1>
        </div>
        <button type="button" class="rounded-md border border-fog px-3 py-2 text-sm lg:hidden" data-toggle-sidebar="#admin-sidebar">Tutup</button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-6">
        @foreach ($menu as $group => $items)
            <div class="space-y-2">
                <p class="px-3 text-xs font-semibold uppercase tracking-[0.16em] text-graphite">{{ $group }}</p>
                <div class="space-y-1">
                    @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-charcoal transition hover:bg-primary-soft hover:text-primary">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>
