<x-layouts.admin title="Master Data Karyawan - {{ config('app.name') }}">
    <div class="space-y-6">

        {{-- ====== HEADER CARD ====== --}}
        <div class="flex flex-col justify-between gap-4 rounded-2xl border border-[#e8e8e8] bg-white p-6 shadow-sm md:flex-row md:items-center md:gap-6 md:px-8 md:py-7">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#024ad8]">MASTER DATA</p>
                <h1 class="mt-1 text-[28px] font-semibold leading-tight tracking-tight text-[#1a1a1a] xl:text-[32px]">Master Data Karyawan</h1>
                <p class="mt-1 text-sm text-[#636363]">Kelola akun dan profil karyawan yang dapat mengakses sistem.</p>
            </div>
            <a href="{{ route('admin.karyawan.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#024ad8] px-5 py-[11px] text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#296ef9] focus:outline-none focus:ring-2 focus:ring-[#024ad8] focus:ring-offset-2" style="height:46px">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Karyawan
            </a>
        </div>

        {{-- ====== ALERTS ====== --}}
        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        {{-- ====== FILTER CARD ====== --}}
        <div class="rounded-2xl border border-[#e8e8e8] bg-white p-6 shadow-sm md:px-8">
            <form method="GET" action="{{ route('admin.karyawan.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="min-w-[220px] flex-1">
                    <x-form.input
                        label="Search karyawan"
                        name="q"
                        type="search"
                        placeholder="Cari nama, username, atau NIP/ID"
                        :value="$query"
                    />
                </div>
                <div class="w-[180px]">
                    <x-form.select
                        label="Divisi"
                        name="division_id"
                        :options="$divisionOptions"
                        placeholder="Semua"
                        :selected="$divisionId"
                    />
                </div>
                <div class="w-[180px]">
                    <x-form.select
                        label="Jabatan"
                        name="position_id"
                        :options="$positionOptions"
                        placeholder="Semua"
                        :selected="$positionId"
                    />
                </div>
                <div class="w-[160px]">
                    <x-form.select
                        label="Status"
                        name="status"
                        :options="$statusOptions"
                        :selected="$status"
                    />
                </div>
                <div class="flex items-end gap-2">
                    <x-button.primary type="submit" class="h-[46px]">Cari / Filter</x-button.primary>
                    <a href="{{ route('admin.karyawan.index') }}" class="inline-flex h-[46px] items-center justify-center rounded-lg border border-[#e8e8e8] bg-white px-5 text-sm font-semibold text-[#636363] transition hover:border-[#024ad8] hover:text-[#024ad8] focus:outline-none focus:ring-2 focus:ring-[#024ad8] focus:ring-offset-2">Reset</a>
                </div>
            </form>
        </div>

        {{-- ====== TABLE CARD ====== --}}
        <div class="overflow-hidden rounded-2xl border border-[#e8e8e8] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="border-b border-[#e8e8e8] bg-[#fafafa]">
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363]">No</th>
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363]">Karyawan</th>
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363]">Role</th>
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363]">NIP/ID</th>
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363]">Divisi / Jabatan</th>
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363]">Status</th>
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363]">Login Terakhir</th>
                            <th class="px-6 py-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-[#636363] text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($employees as $employee)
                            <tr class="border-b border-[#e8e8e8] transition-colors last:border-b-0 hover:bg-[#fafafa]">
                                {{-- No --}}
                                <td class="px-6 py-5 text-sm text-[#636363]">{{ $employees->firstItem() + $loop->index }}</td>

                                {{-- Karyawan --}}
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-[#1a1a1a]">{{ $employee->user->name }}</p>
                                    <p class="text-xs text-[#636363]">{{ $employee->user->username }}</p>
                                </td>

                                {{-- Role --}}
                                <td class="px-6 py-5">
                                    @if ($employee->user->role === 'admin')
                                        <span class="inline-flex items-center rounded-full bg-[#f9d4d2] px-2.5 py-1 text-[11px] font-semibold text-[#b3262b]">Admin</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[#c9e0fc] px-2.5 py-1 text-[11px] font-semibold text-[#024ad8]">Karyawan</span>
                                    @endif
                                </td>

                                {{-- NIP/ID --}}
                                <td class="px-6 py-5 text-sm text-[#3d3d3d]">{{ $employee->employee_number ?: '-' }}</td>

                                {{-- Divisi / Jabatan --}}
                                <td class="px-6 py-5">
                                    <p class="text-sm font-medium text-[#1a1a1a]">{{ $employee->division?->name ?? '-' }}</p>
                                    <p class="text-xs text-[#636363]">{{ $employee->position?->name ?? '-' }}</p>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-5">
                                    @if ($employee->is_active)
                                        <span class="inline-flex items-center rounded-full bg-[#dcfce7] px-2.5 py-1 text-[11px] font-semibold text-[#15803d]">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[#e8e8e8] px-2.5 py-1 text-[11px] font-semibold text-[#636363]">Nonaktif</span>
                                    @endif
                                </td>

                                {{-- Login Terakhir --}}
                                <td class="px-6 py-5 text-sm text-[#636363]">{{ optional($employee->user->last_login_at)->translatedFormat('d M Y, H:i') ?? '-' }}</td>

                                {{-- Aksi --}}
                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-1.5">
                                        {{-- View --}}
                                        <a href="{{ route('admin.karyawan.show', $employee) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#e8e8e8] bg-white text-[#636363] transition hover:border-[#024ad8] hover:text-[#024ad8]" title="Detail">
                                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="1.6"><path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z"/><circle cx="10" cy="10" r="2.2"/></svg>
                                        </a>

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.karyawan.edit', $employee) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#e8e8e8] bg-white text-[#636363] transition hover:border-[#024ad8] hover:text-[#024ad8]" title="Edit">
                                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M4 13.8V16h2.2l8.1-8.1-2.2-2.2L4 13.8Z"/><path d="M11.8 5.7l2.5 2.5" stroke-linecap="round"/></svg>
                                        </a>

                                        {{-- Toggle Status --}}
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#e8e8e8] bg-white text-[#636363] transition hover:border-[#024ad8] hover:text-[#024ad8]" data-modal-open="#toggle-employee-{{ $employee->id }}" title="{{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M10 3.5v4"/><path d="M7.2 5.2a6 6 0 1 0 5.6 0"/></svg>
                                        </button>

                                        {{-- Delete --}}
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#e8e8e8] bg-white text-[#b3262b] transition hover:border-[#b3262b] hover:bg-[#f9d4d2]" data-modal-open="#delete-employee-{{ $employee->id }}" title="Delete Permanen">
                                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M3.5 5.5h13"/><path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5"/><path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5"/><path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke-linejoin="round"/></svg>
                                        </button>
                                    </div>

                                    <x-modal.confirm
                                        id="toggle-employee-{{ $employee->id }}"
                                        :title="$employee->is_active ? 'Nonaktifkan Karyawan' : 'Aktifkan Karyawan'"
                                        :description="$employee->is_active ? 'Akun karyawan ini akan dinonaktifkan.' : 'Akun karyawan ini akan diaktifkan kembali.'"
                                        :confirm-label="$employee->is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                        :danger="$employee->is_active"
                                        :action="route('admin.karyawan.status', $employee)"
                                        method="PATCH"
                                    >
                                        <input type="hidden" name="is_active" value="{{ $employee->is_active ? 0 : 1 }}">
                                    </x-modal.confirm>

                                    <x-modal.confirm
                                        id="delete-employee-{{ $employee->id }}"
                                        title="Delete Permanen Karyawan"
                                        description="Karyawan yang dihapus permanen tidak dapat dikembalikan. Jika sudah punya riwayat training atau test, sistem akan menolak proses ini."
                                        confirm-label="Delete Permanen"
                                        :danger="true"
                                        :action="route('admin.karyawan.destroy', $employee)"
                                        method="DELETE"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16">
                                    <x-empty-state
                                        :title="$hasFilters ? 'Tidak ada karyawan sesuai filter.' : 'Belum ada data karyawan.'"
                                        :description="$hasFilters ? 'Coba ubah kata kunci atau filter yang dipilih.' : 'Tambah karyawan pertama untuk memulai pengelolaan akun.'"
                                        :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Karyawan'"
                                        :action-href="$hasFilters ? route('admin.karyawan.index') : route('admin.karyawan.create')"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ====== TABLE FOOTER ====== --}}
            @if ($employees->hasPages())
                <div class="flex flex-col items-center justify-between gap-3 border-t border-[#e8e8e8] px-6 py-4 sm:flex-row">
                    <p class="text-sm text-[#636363]">
                        Menampilkan {{ $employees->firstItem() }} - {{ $employees->lastItem() }} dari {{ $employees->total() }} data
                    </p>
                    {{ $employees->links() }}
                </div>
            @elseif ($employees->isNotEmpty())
                <div class="flex flex-col items-center justify-between gap-3 border-t border-[#e8e8e8] px-6 py-4 sm:flex-row">
                    <p class="text-sm text-[#636363]">
                        Menampilkan {{ $employees->firstItem() }} - {{ $employees->lastItem() }} dari {{ $employees->total() }} data
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
