@props([
    'trainingOptions',
    'employeeOptions',
    'divisionOptions',
    'positionOptions',
    'targetOptions',
    'action',
    'cancelHref',
])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="assignmentForm(@js(old('target_type', 'employee')))" >
    @csrf

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.select
            label="Training"
            name="training_id"
            :options="$trainingOptions"
            :selected="old('training_id')"
            placeholder="Pilih training published"
        />

        <x-form.select
            label="Target Penugasan"
            name="target_type"
            x-model="targetType"
            :options="$targetOptions"
            :selected="old('target_type', 'employee')"
            placeholder="Pilih target"
        />
    </div>

    <div x-show="targetType === 'employee'" x-cloak class="rounded-2xl border border-fog bg-cloud/40 p-5 space-y-3">
        <h3 class="text-base font-semibold text-ink">Pilih Karyawan Aktif</h3>
        @if ($errors->has('employee_ids'))
            <x-alert variant="danger" title="Validasi Karyawan">{{ $errors->first('employee_ids') }}</x-alert>
        @endif
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ($employeeOptions as $value => $label)
                <label class="inline-flex items-center gap-3 rounded-xl border border-fog bg-white px-4 py-3 text-sm text-charcoal">
                    <input type="checkbox" name="employee_ids[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', old('employee_ids', [])), true)) class="rounded border-fog text-primary focus:ring-primary">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div x-show="targetType === 'division'" x-cloak class="rounded-2xl border border-fog bg-cloud/40 p-5">
        <x-form.select
            label="Divisi"
            name="division_id"
            :options="$divisionOptions"
            :selected="old('division_id')"
            placeholder="Pilih divisi aktif"
        />
    </div>

    <div x-show="targetType === 'position'" x-cloak class="rounded-2xl border border-fog bg-cloud/40 p-5">
        <x-form.select
            label="Jabatan"
            name="position_id"
            :options="$positionOptions"
            :selected="old('position_id')"
            placeholder="Pilih jabatan aktif"
        />
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $cancelHref }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Batal</a>
        <x-button.primary type="submit">Simpan Penugasan</x-button.primary>
    </div>
</form>

<script>
    function assignmentForm(initialTargetType) {
        return {
            targetType: initialTargetType,
        };
    }
</script>
