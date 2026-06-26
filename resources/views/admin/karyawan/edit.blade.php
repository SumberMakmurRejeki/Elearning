<x-layouts.admin title="Edit Karyawan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Edit Karyawan"
            description="Perbarui identitas akun, divisi, jabatan, dan status karyawan."
        >
            <x-button.link href="{{ route('admin.karyawan.show', $employee) }}" variant="text">Lihat Detail</x-button.link>
            <x-button.link href="{{ $backRoute }}" variant="primary">Kembali</x-button.link>
        </x-page.header>

        <x-card.base>
            @include('admin.karyawan.partials.form', [
                'employee' => $employee,
                'user' => $user,
                'action' => route('admin.karyawan.update', $employee),
                'method' => 'PUT',
                'submitLabel' => 'Simpan Perubahan',
                'cancelHref' => $backRoute,
                'divisionOptions' => $divisionOptions,
                'positionOptions' => $positionOptions,
                'statusOptions' => $statusOptions,
                'passwordHelp' => 'Kosongkan bila password tidak diubah.',
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
