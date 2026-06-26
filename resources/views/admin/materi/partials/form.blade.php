@props([
    'material',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
    'cancelHref',
    'trainingOptions',
    'materialTypeOptions',
    'booleanOptions',
])

<form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data">
    @csrf

    @if (strtoupper($method) !== 'POST')
        @method(strtoupper($method))
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.select
            label="Training"
            name="training_id"
            :options="$trainingOptions"
            :selected="old('training_id', (string) $material->training_id)"
            placeholder="Pilih training"
        />

        <x-form.input
            label="Judul Materi"
            name="title"
            placeholder="Contoh: Handbook K3"
            :value="old('title', $material->title)"
        />
    </div>

    <x-form.textarea
        label="Deskripsi Materi"
        name="description"
        rows="5"
        placeholder="Opsional: jelaskan isi materi training"
    >{{ old('description', $material->description) }}</x-form.textarea>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.select
            label="Tipe Sumber Materi"
            name="material_type"
            :options="$materialTypeOptions"
            :selected="old('material_type', $material->material_type ?: 'file')"
        />

        <x-form.input
            label="Urutan Materi"
            name="order_number"
            type="number"
            min="1"
            step="1"
            placeholder="Contoh: 1"
            :value="old('order_number', $material->order_number)"
            help="Opsional. Jika kosong, sistem akan mengurutkan dari tanggal dibuat."
        />

        <div class="space-y-2">
            <x-form.file
                label="Upload File Materi"
                name="file"
                help="Format: PDF, PPT, PPTX, DOC, DOCX, XLS, XLSX, CSV, MP4, JPG, JPEG, PNG, WEBP."
            />

            @if ($material->material_type === 'file' && $material->file_path)
                <p class="text-xs text-graphite">File saat ini: <span class="font-medium text-ink">{{ basename($material->file_path) }}</span></p>
            @endif
        </div>

        <div class="space-y-2">
            <x-form.input
                label="URL Materi"
                name="url"
                type="url"
                placeholder="https://drive.google.com/... atau https://youtube.com/..."
                :value="old('url', $material->url)"
                help="Gunakan untuk Google Drive, YouTube private/unlisted, atau link materi eksternal lain."
            />
        </div>

        <x-form.select
            label="Status"
            name="is_active"
            :options="$booleanOptions"
            :selected="old('is_active', (string) ($material->is_active ? 1 : 0))"
        />
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $cancelHref }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Batal</a>
        <x-button.primary type="submit">{{ $submitLabel }}</x-button.primary>
    </div>
</form>
