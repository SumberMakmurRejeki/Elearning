<x-layouts.admin title="Dashboard Admin - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="EPIC-05 Protected Area"
            title="Dashboard Admin"
            description="Ringkasan kondisi training untuk memantau jumlah karyawan, progress, nilai, dan kelulusan secara cepat."
        />

        <x-card.base>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="grid gap-4 xl:grid-cols-[repeat(3,minmax(0,1fr))_auto]">
                <x-form.select
                    label="Bulan"
                    name="month"
                    :options="$monthOptions"
                    :selected="$selectedMonth"
                />

                <x-form.select
                    label="Tahun"
                    name="year"
                    :options="$yearOptions"
                    :selected="$selectedYear"
                />

                <x-form.select
                    label="Training"
                    name="training_id"
                    :options="$trainingOptions"
                    placeholder="Semua training"
                    :selected="$selectedTrainingId"
                />

                <div class="flex items-end gap-2">
                    <x-button.primary type="submit">Terapkan Filter</x-button.primary>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Reset Filter</a>
                </div>
            </form>
        </x-card.base>

        @if ($hasError)
            <x-error-state
                title="Gagal memuat dashboard"
                description="Data dashboard gagal dimuat"
                :action-label="null"
            />
        @elseif ($hasEmptyState)
            <x-empty-state
                title="Data dashboard belum tersedia"
                description="Tidak ada data sesuai filter yang dipilih"
                :action-label="null"
            />
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($summaryCards as $card)
                    <x-card.stat :label="$card['label']" :value="$card['value']" data-summary="{{ $card['key'] }}">
                        {{ $card['description'] }}
                    </x-card.stat>
                @endforeach
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <x-card.base>
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-ink">Grafik Progress Training</h3>
                            <p class="text-sm text-charcoal">Distribusi status progress untuk training yang sedang difilter.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($progressChart as $row)
                            <div class="space-y-2" data-chart-progress="{{ $row['key'] }}">
                                <div class="flex items-center justify-between gap-4 text-sm">
                                    <span class="font-medium text-ink">{{ $row['label'] }}</span>
                                    <span class="text-graphite">{{ $row['value'] }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-cloud">
                                    <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card.base>

                <x-card.base>
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-ink">Grafik Kelulusan</h3>
                            <p class="text-sm text-charcoal">Perbandingan karyawan lulus dan tidak lulus untuk filter aktif.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($passChart as $row)
                            <div class="space-y-2" data-chart-pass="{{ $row['key'] }}">
                                <div class="flex items-center justify-between gap-4 text-sm">
                                    <span class="font-medium text-ink">{{ $row['label'] }}</span>
                                    <span class="text-graphite">{{ $row['value'] }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-cloud">
                                    <div class="h-full rounded-full {{ $row['key'] === 'passed' ? 'bg-success' : 'bg-warning' }} transition-all" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card.base>
            </div>

            <x-card.base>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-ink">Grafik Nilai Pre-Test vs Post-Test</h3>
                        <p class="text-sm text-charcoal">Perbandingan rata-rata nilai per training pada filter aktif.</p>
                    </div>
                </div>

                @if ($scoreChart === [])
                    <div class="rounded-2xl border border-dashed border-fog bg-cloud/60 px-6 py-12 text-center text-sm text-charcoal">
                        Belum ada data nilai pre-test atau post-test untuk filter ini.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($scoreChart as $row)
                            <div class="rounded-2xl border border-fog bg-cloud/40 p-4" data-chart-score="{{ $row['key'] }}">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-ink">{{ $row['label'] }}</p>
                                        <p class="text-xs text-graphite">Rata-rata pre-test dan post-test</p>
                                    </div>
                                    <div class="text-right text-sm text-charcoal">
                                        <p>Pre: {{ $row['preScore'] === null ? '—' : number_format($row['preScore'], 1, '.', '') }}</p>
                                        <p>Post: {{ $row['postScore'] === null ? '—' : number_format($row['postScore'], 1, '.', '') }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.16em] text-graphite">
                                            <span>Pre-Test</span>
                                            <span>{{ $row['preScore'] === null ? '—' : number_format($row['preScore'], 1, '.', '') }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-white">
                                            <div class="h-full rounded-full bg-primary-soft transition-all" style="width: {{ $row['prePercent'] }}%"></div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.16em] text-graphite">
                                            <span>Post-Test</span>
                                            <span>{{ $row['postScore'] === null ? '—' : number_format($row['postScore'], 1, '.', '') }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-white">
                                            <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $row['postPercent'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card.base>
        @endif
    </div>
</x-layouts.admin>
