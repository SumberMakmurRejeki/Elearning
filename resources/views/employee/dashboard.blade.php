<x-layouts.employee title="Preview Karyawan UI - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="EPIC-04 Protected Area"
            title="Dashboard Karyawan"
            description="Fondasi UI karyawan untuk training saya, riwayat, dan akun pribadi."
        >
            <x-button.outline>Lihat Riwayat</x-button.outline>
            <x-button.primary>Mulai Training</x-button.primary>
        </x-page.header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-card.stat label="Total Training" value="8">Semua training milik karyawan</x-card.stat>
            <x-card.stat label="Sedang Berjalan" value="3">Training aktif saat ini</x-card.stat>
            <x-card.stat label="Lulus" value="4">Training yang sudah lulus</x-card.stat>
            <x-card.stat label="Tidak Lulus" value="1">Butuh retake atau selesai</x-card.stat>
        </div>

        <x-toast variant="info" title="Mode Preview" description="Halaman ini menampilkan card/list flow dan state UI karyawan." />

        <x-card.base>
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-ink">Training Saya</h3>
                    <p class="text-sm text-charcoal">Preview navigation karyawan, card/list, badge, action button.</p>
                </div>
                <x-badge variant="info">Sedang Berjalan</x-badge>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <x-card.base class="border-primary/20 bg-primary-soft/20">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-lg font-semibold text-ink">Training Keselamatan Kerja</h4>
                            <p class="mt-2 text-sm text-charcoal">Materi dasar keselamatan kerja untuk seluruh karyawan.</p>
                        </div>
                        <x-badge variant="info">Berjalan</x-badge>
                    </div>
                    <p class="mt-4 text-sm text-graphite">Deadline: 20 Juli 2026</p>
                    <div class="mt-4 h-2 rounded-full bg-fog">
                        <div class="h-2 w-[70%] rounded-full bg-primary"></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <x-button.primary>Lanjutkan Training</x-button.primary>
                        <x-button.outline>Detail</x-button.outline>
                    </div>
                </x-card.base>

                <x-alert variant="warning" title="Menunggu Penilaian">
                    Jawaban essay sedang menunggu penilaian admin. Nilai akhir akan muncul setelah proses review selesai.
                </x-alert>
            </div>
        </x-card.base>

        <div class="grid gap-6 xl:grid-cols-3">
            <x-empty-state title="Belum ada riwayat training" description="Riwayat training akan muncul setelah training selesai." />
            <x-loading-state :lines="4" />
            <x-error-state title="Gagal memuat training" description="Silakan refresh halaman atau hubungi admin training." action-label="Muat Ulang" />
            <x-success-state title="Progress tersimpan" description="Contoh success state untuk proses selesai pada training karyawan." action-label="Lihat Detail" />
        </div>
    </div>
</x-layouts.employee>
