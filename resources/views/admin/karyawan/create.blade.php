<x-layouts.admin title="Tambah Karyawan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Tambah Karyawan"
            description="Tambahkan akun karyawan baru beserta divisi, jabatan, dan status aktifnya."
        >
            <x-button.link href="{{ $backRoute }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        <x-card.base>
            @include('admin.karyawan.partials.form', [
                'employee' => $employee,
                'user' => $user,
                'action' => route('admin.karyawan.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Karyawan',
                'cancelHref' => $backRoute,
                'divisionOptions' => $divisionOptions,
                'positionOptions' => $positionOptions,
                'statusOptions' => $statusOptions,
                'passwordHelp' => 'Password wajib diisi untuk akun baru.',
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
