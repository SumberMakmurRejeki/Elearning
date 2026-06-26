@props([
    'question',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
    'cancelHref',
    'trainingOptions',
    'testTypeOptions',
    'questionTypeOptions',
    'booleanOptions',
])

@php
    $initialOptions = old('options');

    if (! is_array($initialOptions)) {
        $initialOptions = $question->relationLoaded('options')
            ? $question->options->map(fn ($option) => [
                'option_text' => $option->option_text,
                'is_correct' => $option->is_correct,
            ])->values()->all()
            : [];
    }

    if ($initialOptions === []) {
        $initialOptions = [
            ['option_text' => '', 'is_correct' => false],
            ['option_text' => '', 'is_correct' => false],
        ];
    }
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6"
    x-data="questionForm(@js($initialOptions), @js(old('question_type', $question->question_type ?: 'multiple_choice')))"
>
    @csrf

    @if (strtoupper($method) !== 'POST')
        @method(strtoupper($method))
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.select
            label="Training"
            name="training_id"
            :options="$trainingOptions"
            :selected="old('training_id', (string) $question->training_id)"
            placeholder="Pilih training"
        />

        <x-form.select
            label="Jenis Test"
            name="test_type"
            :options="$testTypeOptions"
            :selected="old('test_type', $question->test_type ?: 'pre_test')"
            placeholder="Pilih jenis test"
        />

        <x-form.select
            label="Jenis Soal"
            name="question_type"
            x-model="questionType"
            :options="$questionTypeOptions"
            :selected="old('question_type', $question->question_type ?: 'multiple_choice')"
            placeholder="Pilih jenis soal"
        />

        <x-form.input
            label="Nomor Soal"
            name="order_number"
            type="number"
            min="1"
            step="1"
            placeholder="Contoh: 1"
            :value="old('order_number', $question->order_number)"
        />

        <x-form.input
            label="Bobot Nilai"
            name="weight"
            type="number"
            min="0.01"
            step="0.01"
            placeholder="Contoh: 10"
            :value="old('weight', $question->weight)"
        />

        <x-form.select
            label="Status"
            name="is_active"
            :options="$booleanOptions"
            :selected="old('is_active', (string) ($question->is_active ? 1 : 0))"
        />
    </div>

    <x-form.textarea
        label="Pertanyaan"
        name="question_text"
        rows="5"
        placeholder="Tulis pertanyaan soal di sini"
    >{{ old('question_text', $question->question_text) }}</x-form.textarea>

    <div x-show="questionType === 'multiple_choice'" x-cloak class="space-y-4 rounded-2xl border border-fog bg-cloud/40 p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-ink">Opsi Jawaban</h3>
                <p class="text-sm text-charcoal">Minimal 2 opsi dan tepat 1 jawaban benar.</p>
            </div>

            <x-button.primary type="button" x-on:click="addOption()">Tambah Opsi</x-button.primary>
        </div>

        @if ($errors->has('options'))
            <x-alert variant="danger" title="Validasi Opsi">{{ $errors->first('options') }}</x-alert>
        @endif

        <template x-for="(option, index) in options" :key="index">
            <div class="grid gap-4 rounded-2xl border border-fog bg-white p-4 md:grid-cols-[minmax(0,1fr)_180px_auto]">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-ink" x-text="`Opsi ${String.fromCharCode(65 + index)}`"></label>
                    <input
                        type="text"
                        class="block w-full rounded-lg border border-fog bg-white px-4 py-2.5 text-sm text-ink placeholder:text-graphite focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        :name="`options[${index}][option_text]`"
                        x-model="option.option_text"
                        placeholder="Isi opsi jawaban"
                    >
                </div>

                <div class="space-y-2">
                    <span class="block text-sm font-medium text-ink">Jawaban Benar</span>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-fog px-4 py-2.5 text-sm text-charcoal">
                        <input type="radio" name="correct_option" :value="index" x-model="correctOption" class="text-primary focus:ring-primary">
                        <span>Pilih sebagai benar</span>
                    </label>
                    <input type="hidden" :name="`options[${index}][is_correct]`" :value="Number(correctOption) === index ? 1 : 0">
                </div>

                <div class="flex items-end justify-end">
                    <x-button.danger type="button" x-on:click="removeOption(index)" x-bind:disabled="options.length <= 2">Hapus</x-button.danger>
                </div>
            </div>
        </template>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $cancelHref }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Batal</a>
        <x-button.primary type="submit">{{ $submitLabel }}</x-button.primary>
    </div>
</form>

<script>
    function questionForm(initialOptions, initialType) {
        const normalizedOptions = Array.isArray(initialOptions) && initialOptions.length > 0
            ? initialOptions.map((option) => ({
                option_text: option.option_text ?? '',
                is_correct: Boolean(Number(option.is_correct ?? 0)),
            }))
            : [
                { option_text: '', is_correct: false },
                { option_text: '', is_correct: false },
            ];

        const initialCorrect = normalizedOptions.findIndex((option) => option.is_correct);

        return {
            questionType: initialType,
            options: normalizedOptions,
            correctOption: initialCorrect >= 0 ? initialCorrect : 0,
            addOption() {
                this.options.push({ option_text: '', is_correct: false });
            },
            removeOption(index) {
                if (this.options.length <= 2) {
                    return;
                }

                this.options.splice(index, 1);

                if (Number(this.correctOption) === index) {
                    this.correctOption = 0;
                } else if (Number(this.correctOption) > index) {
                    this.correctOption = Number(this.correctOption) - 1;
                }
            },
        };
    }
</script>
