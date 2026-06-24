<x-layouts.admin title="Preview Admin UI - {{ config('app.name') }}">
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-card.stat label="Total Karyawan" value="128">Data karyawan aktif perusahaan</x-card.stat>
            <x-card.stat label="Total Training" value="24">Termasuk draft, published, archived</x-card.stat>
            <x-card.stat label="Training Aktif" value="12">Sedang berjalan bulan ini</x-card.stat>
            <x-card.stat label="Rata-rata Nilai" value="84">Rata-rata post-test</x-card.stat>
        </div>

        <x-alert title="UI Foundation Siap" variant="success">
            Layout admin, sidebar, topbar, komponen form, table, card, badge, modal, dan state sudah dibuat.
        </x-alert>

        <x-card.base>
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-ink">Contoh Table Karyawan</h3>
                    <p class="text-sm text-charcoal">Preview komponen table, badge, action button, modal.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button.outline>Reset Filter</x-button.outline>
                    <x-button.primary>Tambah Karyawan</x-button.primary>
                </div>
            </div>

            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Divisi</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>
                <tbody>
                    <x-table.row>
                        <td class="px-6 py-4 font-medium text-ink">Budi Santoso</td>
                        <td class="px-6 py-4">budi01</td>
                        <td class="px-6 py-4">HRD</td>
                        <td class="px-6 py-4"><x-badge variant="success">Aktif</x-badge></td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <x-button.icon label="Detail">👁</x-button.icon>
                                <x-button.icon label="Edit">✎</x-button.icon>
                                <x-button.icon label="Hapus" data-modal-open="#delete-preview">🗑</x-button.icon>
                            </div>
                        </td>
                    </x-table.row>
                </tbody>
            </x-table.table>
        </x-card.base>

        <div class="grid gap-6 xl:grid-cols-3">
            <x-empty-state title="Belum ada data laporan" description="State kosong untuk modul laporan atau monitoring." action-label="Reset Filter" />
            <x-loading-state :lines="5" />
            <x-error-state title="Gagal memuat dashboard" description="Contoh state error reusable untuk semua halaman admin." />
        </div>
    </div>

    <x-modal.confirm id="delete-preview" title="Delete Permanen Karyawan" description="Data yang dihapus permanen tidak dapat dikembalikan. Lanjutkan?" confirm-label="Delete Permanen" :danger="true" />
</x-layouts.admin>
