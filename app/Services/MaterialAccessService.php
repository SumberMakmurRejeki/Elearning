<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTrainingProgress;
use App\Models\MaterialAccessLog;
use App\Models\Training;
use App\Models\TrainingMaterial;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class MaterialAccessService
{
    /**
     * @return array<string, mixed>
     */
    public function buildMaterialListForUser(User $user, Training $training): ?array
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return null;
        }

        if (! $this->employeeHasTraining($employee, $training)) {
            return null;
        }

        $progress = EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->first();

        $activeMaterials = TrainingMaterial::query()
            ->where('training_id', $training->id)
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN order_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('order_number')
            ->orderByDesc('created_at')
            ->get();

        $accessedIds = MaterialAccessLog::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->pluck('material_id')
            ->flip();

        $materials = $activeMaterials->map(function (TrainingMaterial $material) use ($accessedIds) {
            return [
                'material' => $material,
                'isAccessed' => $accessedIds->has($material->id),
                'fileSizeLabel' => $material->file_size !== null ? $this->formatFileSize($material->file_size) : null,
            ];
        });

        return [
            'employee' => $employee->loadMissing('user'),
            'training' => $training,
            'progress' => $progress,
            'materials' => $materials,
            'hasEmptyState' => $materials->isEmpty(),
        ];
    }

    public function recordAccess(User $user, Training $training, TrainingMaterial $material): bool
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return false;
        }

        if (! $this->employeeHasTraining($employee, $training)) {
            return false;
        }

        if ($material->training_id !== $training->id) {
            return false;
        }

        if (! $material->is_active) {
            return false;
        }

        try {
            MaterialAccessLog::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'material_id' => $material->id,
                ],
                [
                    'training_id' => $training->id,
                    'opened_at' => Carbon::now(),
                ]
            );

            $this->updateMaterialProgress($employee, $training);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function guardFileAccess(User $user, Training $training, TrainingMaterial $material): ?array
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return null;
        }

        if (! $this->employeeHasTraining($employee, $training)) {
            return null;
        }

        if ($material->training_id !== $training->id) {
            return null;
        }

        if (! $material->is_active) {
            return null;
        }

        if ($material->material_type !== 'file') {
            return null;
        }

        if ($material->file_path === null) {
            return null;
        }

        return [
            'employee' => $employee,
            'training' => $training,
            'material' => $material,
        ];
    }

    private function employeeHasTraining(Employee $employee, Training $training): bool
    {
        return EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->exists();
    }

    private function updateMaterialProgress(Employee $employee, Training $training): void
    {
        $activeMaterialCount = (int) DB::table('training_materials')
            ->where('training_id', $training->id)
            ->where('is_active', true)
            ->count();

        if ($activeMaterialCount === 0) {
            return;
        }

        $accessedCount = (int) DB::table('material_access_logs')
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->whereIn('material_id', function ($query) use ($training): void {
                $query->select('id')
                    ->from('training_materials')
                    ->where('training_id', $training->id)
                    ->where('is_active', true);
            })
            ->count();

        if ($accessedCount < $activeMaterialCount) {
            return;
        }

        EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->whereNull('material_completed_at')
            ->update([
                'material_completed_at' => Carbon::now(),
                'status' => DB::raw("CASE WHEN status = 'not_started' OR status = 'pre_test_completed' OR status = 'in_progress' OR status = 'in_material' THEN 'material_completed' ELSE status END"),
            ]);
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }
}
