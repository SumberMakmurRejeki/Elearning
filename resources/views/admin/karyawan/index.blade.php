<x-layouts.admin title="Master Data Karyawan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Master Data Karyawan"
            description="Kelola akun dan profil karyawan yang dapat mengakses sistem."
        >
            <x-button.link href="{{ route('admin.karyawan.create') }}" variant="primary">Tambah Karyawan</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <form method="GET" action="{{ route('admin.karyawan.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(180px,0.6fr)_minmax(180px,0.6fr)_minmax(180px,0.6fr)_auto]">
                <x-form.input
                    label="Search karyawan"
                    name="q"
                    type="search"
                    placeholder="Cari nama, username, atau NIP/ID"
                    :value="$query"
                />

                <x-form.select
                    label="Divisi"
                    name="division_id"
                    :options="$divisionOptions"
                    placeholder="Semua"
                    :selected="$divisionId"
                />

                <x-form.select
                    label="Jabatan"
                    name="position_id"
                    :options="$positionOptions"
                    placeholder="Semua"
                    :selected="$positionId"
                />

                <x-form.select
                    label="Status"
                    name="status"
                    :options="$statusOptions"
                    :selected="$status"
                />

                <div class="flex items-end gap-2">
                    <x-button.primary type="submit">Cari / Filter</x-button.primary>
                    <x-button.link href="{{ route('admin.karyawan.index') }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        <x-card.base class="p-0">
            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">NIP/ID</th>
                        <th class="px-6 py-4">Divisi / Jabatan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Login Terakhir</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>

                <tbody>
                    @forelse ($employees as $employee)
                        <x-table.row>
                            <td class="px-6 py-4 text-sm text-graphite">{{ $employees->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <p class="font-medium text-ink">{{ $employee->user->name }}</p>
                                    <p class="text-sm text-charcoal">{{ $employee->user->username }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $employee->employee_number ?: '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="space-y-1 text-sm text-charcoal">
                                    <p class="font-medium text-ink">{{ $employee->division?->name ?? '-' }}</p>
                                    <p>{{ $employee->position?->name ?? '-' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$employee->is_active ? 'success' : 'neutral'">{{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($employee->user->last_login_at)->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <x-button.link href="{{ route('admin.karyawan.show', $employee) }}" variant="icon" aria-label="Detail {{ $employee->user->name }}" title="Detail">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/>
                                            <circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/>
                                        </svg>
                                    </x-button.link>

                                    <x-button.link href="{{ route('admin.karyawan.edit', $employee) }}" variant="icon" aria-label="Edit {{ $employee->user->name }}" title="Edit">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M4 13.8V16h2.2l8.1-8.1-2.2-2.2L4 13.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M11.8 5.7l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </x-button.link>

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-employee-{{ $employee->id }}" aria-label="{{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $employee->user->name }}" title="{{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M10 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.2 5.2a6 6 0 1 0 5.6 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-danger shadow-sm transition hover:border-danger hover:text-danger focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-employee-{{ $employee->id }}" aria-label="Delete permanen {{ $employee->user->name }}" title="Delete Permanen">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M3.5 5.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                        </svg>
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
                        </x-table.row>
                    @empty
                        <x-table.empty
                            colspan="7"
                            :title="$hasFilters ? 'Tidak ada karyawan sesuai filter.' : 'Belum ada data karyawan.'"
                            :description="$hasFilters ? 'Coba ubah kata kunci atau filter yang dipilih.' : 'Tambah karyawan pertama untuk memulai pengelolaan akun.'"
                            :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Karyawan'"
                            :action-href="$hasFilters ? route('admin.karyawan.index') : route('admin.karyawan.create')"
                        />
                    @endforelse
                </tbody>
            </x-table.table>

            @if ($employees->hasPages())
                <div class="border-t border-fog px-6 py-4">
                    {{ $employees->links() }}
                </div>
            @endif
        </x-card.base>
    </div>
</x-layouts.admin>
