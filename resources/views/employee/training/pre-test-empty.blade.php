<x-layouts.employee title="Pre-Test - {{ $training->title }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Pre-Test"
            title="{{ $training->title }}"
            description="Informasi pre-test training."
        >
            <x-button.link href="{{ route('employee.training.show', $training) }}">Kembali ke Detail</x-button.link>
        </x-page.header>

        @if (session('info'))
            <x-alert variant="info" title="Informasi">{{ session('info') }}</x-alert>
        @endif

        <x-empty-state
            title="Tidak ada soal pre-test aktif."
            description="Training ini memiliki pre-test, namun belum ada soal aktif yang tersedia. Silakan hubungi admin."
            action-label="Kembali ke Detail"
            :action-href="route('employee.training.show', $training)"
        />
    </div>
</x-layouts.employee>
