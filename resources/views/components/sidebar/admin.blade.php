@php
    $currentRoute = Route::currentRouteName();

    $menu = [
        [
            'group' => 'Dashboard',
            'icon' => '<path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/>',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ],
        ],
        [
            'group' => 'Master Data',
            'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'items' => [
                ['label' => 'Karyawan', 'route' => 'admin.karyawan.index'],
                ['label' => 'Divisi', 'route' => 'admin.divisi.index'],
                ['label' => 'Jabatan', 'route' => 'admin.jabatan.index'],
            ],
        ],
        [
            'group' => 'Master Training',
            'icon' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
            'items' => [
                ['label' => 'Daftar Training', 'route' => 'admin.training.index'],
                ['label' => 'Materi Training', 'route' => 'admin.materi.index'],
                ['label' => 'Soal Test', 'route' => 'admin.soal.index'],
                ['label' => 'Penugasan Training', 'route' => 'admin.penugasan.index'],
            ],
        ],
        [
            'group' => 'Penilaian',
            'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
            'items' => [
                ['label' => 'Jawaban Essay', 'route' => 'admin.essay-answers.index'],
                ['label' => 'Hasil Test', 'route' => 'admin.hasil-test.index'],
            ],
        ],
        [
            'group' => 'Monitoring & Laporan',
            'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
            'items' => [
                ['label' => 'Progress Training', 'route' => 'admin.progress.index'],
                ['label' => 'Laporan', 'route' => 'admin.laporan.index'],
                ['label' => 'Export Data', 'route' => 'admin.export.index'],
            ],
        ],
        [
            'group' => 'Pengaturan User',
            'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'items' => [
                ['label' => 'Profil & Password', 'route' => 'admin.profile.show'],
            ],
        ],
    ];

    $isActive = fn (string $route): bool => $currentRoute === $route;
    $isGroupActive = fn (array $group): bool => collect($group['items'])->contains(fn (array $item): bool => $isActive($item['route']));
@endphp

<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-[260px] -translate-x-full flex-col border-r border-[#e8e8e8] bg-white transition-transform duration-200 lg:translate-x-0">
    {{-- Logo / Brand --}}
    <div class="flex items-center gap-3 border-b border-[#e8e8e8] px-6 py-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#024ad8]">
            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-[#1a1a1a]">E-Learning Training</p>
            <p class="text-[11px] text-[#636363]">Admin Panel</p>
        </div>
        <button type="button" class="ml-auto rounded-md border border-[#e8e8e8] px-2.5 py-1.5 text-xs lg:hidden" data-toggle-sidebar="#admin-sidebar">Tutup</button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach ($menu as $section)
            @php($groupActive = $isGroupActive($section))

            {{-- Group header --}}
            <div class="mb-1 mt-4 flex items-center gap-2 px-3">
                <svg class="h-4 w-4 text-[#636363]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $section['icon'] !!}</svg>
                <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#636363]">{{ $section['group'] }}</span>
            </div>

            {{-- Group items --}}
            <div class="space-y-0.5">
                @foreach ($section['items'] as $item)
                    @php($active = $isActive($item['route']))
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center rounded-lg px-3 py-2 text-[13px] font-medium transition-colors
                              {{ $active ? 'bg-[#c9e0fc] text-[#024ad8]' : 'text-[#3d3d3d] hover:bg-[#c9e0fc]/50 hover:text-[#024ad8]' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>
</aside>
