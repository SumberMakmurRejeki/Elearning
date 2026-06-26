<x-layouts.admin title="Tambah Penugasan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header eyebrow="Master Training" title="Tambah Penugasan" description="Tugaskan training published ke karyawan tertentu, seluruh divisi, atau seluruh jabatan aktif.">
            <x-button.link href="{{ route('admin.penugasan.index') }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            @include('admin.penugasan.partials.form', [
                'trainingOptions' => $trainingOptions,
                'employeeOptions' => $employeeOptions,
                'divisionOptions' => $divisionOptions,
                'positionOptions' => $positionOptions,
                'targetOptions' => $targetOptions,
                'action' => route('admin.penugasan.store'),
                'cancelHref' => $backRoute,
            ])
        </x-card.base>
    </div>
</x-layouts.admin>
