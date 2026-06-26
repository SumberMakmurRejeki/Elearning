@props([
    'position',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
    'cancelHref',
    'statusOptions',
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf

    @if (strtoupper($method) !== 'POST')
        @method(strtoupper($method))
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.input
            label="Nama Jabatan"
            name="name"
            placeholder="Contoh: Supervisor"
            :value="old('name', $position->name)"
        />

        <x-form.select
            label="Status"
            name="is_active"
            :options="$statusOptions"
            :selected="old('is_active', (string) ($position->is_active ? 1 : 0))"
        />
    </div>

    <x-form.textarea
        label="Deskripsi"
        name="description"
        rows="5"
        placeholder="Opsional: jelaskan fungsi jabatan"
    >{{ old('description', $position->description) }}</x-form.textarea>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $cancelHref }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Batal</a>
        <x-button.primary type="submit">{{ $submitLabel }}</x-button.primary>
    </div>
</form>
