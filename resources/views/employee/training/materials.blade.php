<x-layouts.employee title="Materi Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Portal Karyawan"
            title="Materi Training"
            description="Daftar materi aktif untuk training ini. Setiap materi akan ditandai sudah dibuka atau belum."
        >
            <x-button.link href="{{ route('employee.training.show', $training) }}">Kembali ke Detail</x-button.link>
        </x-page.header>

        @if ($hasError)
            <x-error-state title="Gagal memuat materi" description="Daftar materi gagal dimuat." action-label="Muat Ulang" />
        @else
            @if ($hasEmptyState)
                <x-empty-state
                    title="Belum ada materi aktif."
                    description="Materi training akan tampil di sini setelah admin menambahkan materi aktif."
                    action-label="Kembali ke Detail"
                    :action-href="route('employee.training.show', $training)"
                />
            @else
                <div class="grid gap-4">
                    @foreach ($materials as $item)
                        @php
                            $material = $item['material'];
                            $isAccessed = $item['isAccessed'];
                        @endphp
                        <x-card.base class="{{ $isAccessed ? 'border-success/20 bg-success-soft/10' : '' }}">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="flex-1 space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-badge :variant="$isAccessed ? 'success' : 'neutral'">
                                            {{ $isAccessed ? 'Sudah Dibuka' : 'Belum Dibuka' }}
                                        </x-badge>
                                        <x-badge variant="info">
                                            {{ $material->material_type === 'file' ? strtoupper($material->file_type ?? 'FILE') : 'LINK' }}
                                        </x-badge>
                                    </div>
                                    <h3 class="text-lg font-semibold text-ink">{{ $material->title }}</h3>
                                    <p class="text-sm text-charcoal">{{ $material->description ?: 'Tidak ada deskripsi materi.' }}</p>
                                    @if ($item['fileSizeLabel'])
                                        <p class="text-xs text-graphite">Ukuran: {{ $item['fileSizeLabel'] }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if ($material->material_type === 'file')
                                        <x-button.link href="{{ route('employee.material.view', [$training, $material]) }}" variant="primary">Buka Materi</x-button.link>
                                        <x-button.outline href="{{ route('employee.material.download', [$training, $material]) }}">Download</x-button.outline>
                                    @else
                                        <x-button.link href="{{ route('employee.material.open-link', [$training, $material]) }}" variant="primary">Buka Link</x-button.link>
                                    @endif
                                </div>
                            </div>
                        </x-card.base>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-layouts.employee>
