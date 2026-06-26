<x-layouts.employee title="Training Saya - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Portal Karyawan"
            title="Training Saya"
            description="Daftar training yang ditugaskan kepada Anda. Gunakan search dan filter untuk menemukan progress tertentu dengan cepat."
        />

        @if ($hasError)
            <x-error-state title="Gagal memuat training" description="Data training saya gagal dimuat." action-label="Muat Ulang" />
        @else
            <x-card.base>
                <form method="GET" action="{{ route('employee.training.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto] lg:items-end">
                    <div class="space-y-2">
                        <label for="q" class="text-sm font-semibold text-ink">Search Training</label>
                        <input
                            id="q"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari nama atau deskripsi training"
                            class="w-full rounded-lg border border-fog bg-white px-4 py-2.5 text-sm text-ink shadow-sm outline-none transition placeholder:text-graphite focus:border-primary focus:ring-2 focus:ring-primary/20"
                        >
                    </div>

                    <div class="space-y-2">
                        <label for="status" class="text-sm font-semibold text-ink">Status Progress</label>
                        <select id="status" name="status" class="w-full rounded-lg border border-fog bg-white px-4 py-2.5 text-sm text-ink shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <x-button.primary type="submit">Terapkan</x-button.primary>
                        <x-button.link href="{{ route('employee.training.index') }}">Reset</x-button.link>
                    </div>
                </form>
            </x-card.base>

            @if ($hasEmptyState)
                <x-empty-state
                    title="Belum ada training yang ditugaskan."
                    description="Training yang Anda miliki akan muncul di sini setelah admin membuat penugasan."
                    action-label="Kembali ke Dashboard"
                    :action-href="route('employee.dashboard')"
                />
            @else
                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach ($trainings as $item)
                        @php
                            $training = $item['training'];
                            $statusVariant = $item['statusVariant'];
                            $trainingVariant = $item['trainingStatusVariant'];
                            $stepCards = $item['stepCards'];
                        @endphp

                        <x-card.base class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap gap-2">
                                        <x-badge :variant="$statusVariant">{{ $item['statusLabel'] }}</x-badge>
                                        <x-badge :variant="$trainingVariant">{{ $item['trainingStatusLabel'] }}</x-badge>
                                    </div>
                                    <h2 class="text-xl font-semibold text-ink">{{ $training?->title ?? '-' }}</h2>
                                    <p class="text-sm leading-6 text-charcoal">{{ \Illuminate\Support\Str::limit($training?->description ?: 'Tidak ada deskripsi training.', 120) }}</p>
                                </div>
                            </div>

                            <dl class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-cloud/70 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Ditugaskan</dt>
                                    <dd class="mt-2 text-sm font-medium text-ink">{{ $item['assignedAt'] }}</dd>
                                </div>
                                <div class="rounded-2xl bg-cloud/70 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Deadline</dt>
                                    <dd class="mt-2 text-sm font-medium text-ink">{{ $item['deadline'] }}</dd>
                                </div>
                            </dl>

                            <div class="grid gap-3 sm:grid-cols-4">
                                @foreach ($stepCards as $step)
                                    @php
                                        $stepClasses = match ($step['status']) {
                                            'done' => 'border-success/20 bg-success-soft/60 text-success',
                                            'current' => 'border-primary/20 bg-primary-soft/60 text-primary',
                                            'locked' => 'border-fog bg-cloud text-graphite',
                                            default => 'border-fog bg-cloud text-charcoal',
                                        };
                                    @endphp
                                    <div class="rounded-2xl border p-3 {{ $stepClasses }}">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em]">{{ $step['label'] }}</p>
                                        <p class="mt-2 text-sm font-semibold">{{ $step['value'] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <x-button.link href="{{ route('employee.training.show', $training) }}" variant="primary">Lihat Detail</x-button.link>
                            </div>
                        </x-card.base>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-layouts.employee>
