<x-layouts.employee title="Pre-Test - {{ $training->title }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Pre-Test"
            title="{{ $training->title }}"
            description="Kerjakan semua soal pre-test berikut. Pastikan setiap soal terjawab sebelum submit."
        >
            <x-button.link href="{{ route('employee.training.show', $training) }}">Kembali ke Detail</x-button.link>
        </x-page.header>

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <form method="POST" action="{{ route('employee.pre-test.submit', $training) }}" class="space-y-6">
            @csrf

            @foreach ($questions as $index => $question)
                <x-card.base class="space-y-4" id="soal-{{ $question->id }}">
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1 space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-badge variant="info">{{ $question->question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}</x-badge>
                                <span class="text-xs text-graphite">Bobot: {{ number_format((float) $question->weight, 0) }}</span>
                            </div>
                            <p class="text-base font-medium text-ink whitespace-pre-line">{{ $question->question_text }}</p>

                            @if ($question->question_type === 'multiple_choice')
                                <div class="space-y-2">
                                    @foreach ($question->options as $option)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-fog bg-white p-4 transition hover:border-primary/40 has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30">
                                            <input
                                                type="radio"
                                                name="answers[{{ $index }}][question_id]"
                                                value="{{ $question->id }}"
                                                class="mt-1 h-4 w-4 text-primary focus:ring-primary"
                                                required
                                                hidden
                                            >
                                            <input
                                                type="radio"
                                                name="answers[{{ $index }}][selected_option_id]"
                                                value="{{ $option->id }}"
                                                class="mt-1 h-4 w-4 text-primary focus:ring-primary"
                                                required
                                            >
                                            <div class="flex-1">
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-fog text-sm font-semibold text-charcoal">{{ $option->option_label }}</span>
                                                <span class="ml-3 text-sm text-ink">{{ $option->option_text }}</span>
                                            </div>
                                        </label>

                                        {{-- Hidden field to always send question_id when an option is selected --}}
                                    @endforeach
                                </div>
                            @else
                                <textarea
                                    name="answers[{{ $index }}][essay_answer]"
                                    rows="4"
                                    placeholder="Tulis jawaban Anda di sini..."
                                    class="w-full rounded-lg border border-fog bg-white px-4 py-3 text-sm text-ink shadow-sm outline-none transition placeholder:text-graphite focus:border-primary focus:ring-2 focus:ring-primary/20"
                                    required
                                ></textarea>
                            @endif

                            {{-- Always include question_id for each question --}}
                            @if ($question->question_type === 'multiple_choice')
                                <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                            @else
                                <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                            @endif
                        </div>
                    </div>
                </x-card.base>
            @endforeach

            <div class="flex justify-end gap-3">
                <x-button.link href="{{ route('employee.training.show', $training) }}">Batal</x-button.link>
                <x-button.primary type="submit">Submit Pre-Test</x-button.primary>
            </div>
        </form>
    </div>
</x-layouts.employee>
