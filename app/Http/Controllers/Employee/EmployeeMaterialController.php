<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingMaterial;
use App\Services\MaterialAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class EmployeeMaterialController extends Controller
{
    public function index(Training $training, MaterialAccessService $service): View
    {
        $user = auth()->user();
        $data = $service->buildMaterialListForUser($user, $training);

        abort_if($data === null, 404);

        return view('employee.training.materials', $data + ['hasError' => false]);
    }

    public function view(Training $training, TrainingMaterial $material, MaterialAccessService $service): BinaryFileResponse|RedirectResponse
    {
        $user = auth()->user();
        $guard = $service->guardFileAccess($user, $training, $material);

        if ($guard === null) {
            return redirect()
                ->route('employee.material.index', $training)
                ->with('error', 'Materi tidak ditemukan atau tidak aktif.');
        }

        $disk = Storage::disk('local');
        $filePath = $disk->path($guard['material']->file_path);

        if (! $disk->exists($guard['material']->file_path)) {
            return redirect()
                ->route('employee.material.index', $training)
                ->with('error', 'File materi tidak ditemukan pada penyimpanan.');
        }

        $service->recordAccess($user, $training, $material);

        return response()->file($filePath, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function download(Training $training, TrainingMaterial $material, MaterialAccessService $service): RedirectResponse|StreamedResponse
    {
        $user = auth()->user();
        $guard = $service->guardFileAccess($user, $training, $material);

        if ($guard === null) {
            return redirect()
                ->route('employee.material.index', $training)
                ->with('error', 'Materi tidak ditemukan atau tidak aktif.');
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($guard['material']->file_path)) {
            return redirect()
                ->route('employee.material.index', $training)
                ->with('error', 'File materi tidak ditemukan pada penyimpanan.');
        }

        $service->recordAccess($user, $training, $material);

        return $disk->download(
            $guard['material']->file_path,
            $guard['material']->title.'.'.$guard['material']->file_type,
            ['Content-Disposition' => 'attachment']
        );
    }

    public function openLink(Training $training, TrainingMaterial $material, MaterialAccessService $service): RedirectResponse
    {
        $user = auth()->user();

        if (! $user?->employee) {
            abort(404);
        }

        if ($material->training_id !== $training->id || ! $material->is_active || $material->material_type !== 'link' || $material->url === null) {
            abort(404);
        }

        $employee = $user->employee;

        $hasAccess = \App\Models\EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->exists();

        if (! $hasAccess) {
            abort(404);
        }

        try {
            \App\Models\MaterialAccessLog::updateOrCreate(
                ['employee_id' => $employee->id, 'material_id' => $material->id],
                ['training_id' => $training->id, 'opened_at' => now()]
            );

            $activeCount = (int) \Illuminate\Support\Facades\DB::table('training_materials')
                ->where('training_id', $training->id)
                ->where('is_active', true)
                ->count();

            if ($activeCount > 0) {
                $accessedCount = (int) \Illuminate\Support\Facades\DB::table('material_access_logs')
                    ->where('employee_id', $employee->id)
                    ->where('training_id', $training->id)
                    ->whereIn('material_id', function ($query) use ($training): void {
                        $query->select('id')->from('training_materials')->where('training_id', $training->id)->where('is_active', true);
                    })
                    ->count();

                if ($accessedCount >= $activeCount) {
                    \App\Models\EmployeeTrainingProgress::query()
                        ->where('employee_id', $employee->id)
                        ->where('training_id', $training->id)
                        ->whereNull('material_completed_at')
                        ->update([
                            'material_completed_at' => now(),
                            'status' => \Illuminate\Support\Facades\DB::raw("CASE WHEN status IN ('not_started','pre_test_completed','in_progress','in_material') THEN 'material_completed' ELSE status END"),
                        ]);
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return redirect()->away($material->url);
    }
}
