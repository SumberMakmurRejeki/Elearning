<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\TrainingAssignmentController;
use App\Http\Controllers\Admin\TrainingMaterialController;
use App\Http\Controllers\Admin\TrainingController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeTrainingController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\PositionController;
use Illuminate\Support\Facades\Route;

$placeholder = static function (string $role, string $title, string $description): \Closure {
    return static function () use ($role, $title, $description) {
        return view('placeholders.section', [
            'role' => $role,
            'title' => $title,
            'description' => $description,
        ]);
    };
};

Route::get('/', static function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('employee.dashboard');
});

Route::get('/ui-preview/login', [LoginController::class, 'show'])->name('preview.login');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active', 'role:admin'])->group(function () use ($placeholder) {
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    Route::get('/ui-preview/admin', AdminDashboardController::class)->name('preview.admin');

    Route::patch('/admin/divisi/{division}/status', [DivisionController::class, 'updateStatus'])->name('admin.divisi.status');
    Route::resource('/admin/divisi', DivisionController::class)
        ->names('admin.divisi')
        ->parameters(['divisi' => 'division']);

    Route::patch('/admin/jabatan/{position}/status', [PositionController::class, 'updateStatus'])->name('admin.jabatan.status');
    Route::resource('/admin/jabatan', PositionController::class)
        ->names('admin.jabatan')
        ->parameters(['jabatan' => 'position']);

    Route::patch('/admin/karyawan/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('admin.karyawan.status');
    Route::post('/admin/karyawan/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('admin.karyawan.reset-password');
    Route::resource('/admin/karyawan', EmployeeController::class)
        ->names('admin.karyawan')
        ->parameters(['karyawan' => 'employee']);

    Route::patch('/admin/training/{training}/status', [TrainingController::class, 'updateStatus'])->name('admin.training.status');
    Route::resource('/admin/training', TrainingController::class)
        ->names('admin.training')
        ->parameters(['training' => 'training']);

    Route::patch('/admin/materi-training/{training_material}/status', [TrainingMaterialController::class, 'updateStatus'])->name('admin.materi.status');
    Route::get('/admin/materi-training/{training_material}/preview', [TrainingMaterialController::class, 'previewFile'])->name('admin.materi.preview-file');
    Route::get('/admin/materi-training/{training_material}/download', [TrainingMaterialController::class, 'downloadFile'])->name('admin.materi.download-file');
    Route::resource('/admin/materi-training', TrainingMaterialController::class)
        ->names('admin.materi')
        ->parameters(['materi-training' => 'training_material']);

    Route::patch('/admin/soal-test/{question}/status', [QuestionController::class, 'updateStatus'])->name('admin.soal.status');
    Route::resource('/admin/soal-test', QuestionController::class)
        ->names('admin.soal')
        ->parameters(['soal-test' => 'question']);

    Route::get('/admin/penugasan-training', [TrainingAssignmentController::class, 'index'])->name('admin.penugasan.index');
    Route::get('/admin/penugasan-training/create', [TrainingAssignmentController::class, 'create'])->name('admin.penugasan.create');
    Route::post('/admin/penugasan-training', [TrainingAssignmentController::class, 'store'])->name('admin.penugasan.store');
    Route::get('/admin/penugasan-training/{training_progress}', [TrainingAssignmentController::class, 'show'])->name('admin.penugasan.show');
    Route::delete('/admin/penugasan-training/{training_progress}', [TrainingAssignmentController::class, 'destroy'])->name('admin.penugasan.destroy');

    Route::get('/ui-preview/admin/karyawan', [EmployeeController::class, 'index'])->name('preview.admin.karyawan');
    Route::get('/ui-preview/admin/divisi', [DivisionController::class, 'index'])->name('preview.admin.divisi');
    Route::get('/ui-preview/admin/jabatan', [PositionController::class, 'index'])->name('preview.admin.jabatan');
    Route::get('/ui-preview/admin/daftar-training', $placeholder('admin', 'Daftar Training', 'Placeholder untuk halaman daftar training.'))->name('preview.admin.training');
    Route::get('/ui-preview/admin/materi-training', $placeholder('admin', 'Materi Training', 'Placeholder untuk halaman materi training.'))->name('preview.admin.materi');
    Route::get('/ui-preview/admin/soal-test', $placeholder('admin', 'Soal Test', 'Placeholder untuk halaman soal test.'))->name('preview.admin.soal');
    Route::get('/ui-preview/admin/penugasan-training', $placeholder('admin', 'Penugasan Training', 'Placeholder untuk halaman penugasan training.'))->name('preview.admin.penugasan');
    Route::get('/ui-preview/admin/jawaban-essay', $placeholder('admin', 'Jawaban Essay', 'Placeholder untuk halaman penilaian jawaban essay.'))->name('admin.jawaban-essay.index');
    Route::get('/ui-preview/admin/hasil-test', $placeholder('admin', 'Hasil Test', 'Placeholder untuk halaman hasil test.'))->name('admin.hasil-test.index');
    Route::get('/ui-preview/admin/progress-training', $placeholder('admin', 'Progress Training', 'Placeholder untuk halaman monitoring progress training.'))->name('admin.progress.index');
    Route::get('/ui-preview/admin/laporan', $placeholder('admin', 'Laporan', 'Placeholder untuk halaman laporan.'))->name('admin.laporan.index');
    Route::get('/ui-preview/admin/export-data', $placeholder('admin', 'Export Data', 'Placeholder untuk halaman export data.'))->name('admin.export.index');
    Route::get('/ui-preview/admin/profil', $placeholder('admin', 'Profil Admin', 'Placeholder untuk halaman profil admin.'))->name('admin.profile.index');
    Route::get('/ui-preview/admin/ubah-password', $placeholder('admin', 'Ubah Password', 'Placeholder untuk halaman ubah password admin.'))->name('admin.password.index');
});

Route::middleware(['auth', 'active', 'role:karyawan'])->group(function () use ($placeholder) {
    Route::get('/karyawan/dashboard', EmployeeDashboardController::class)->name('employee.dashboard');
    Route::get('/ui-preview/employee', EmployeeDashboardController::class)->name('preview.employee');

    Route::get('/karyawan/training-saya', [EmployeeTrainingController::class, 'index'])->name('employee.training.index');
    Route::get('/karyawan/training-saya/{training}', [EmployeeTrainingController::class, 'show'])->name('employee.training.show');
    Route::get('/karyawan/training-saya/{training}/{action}', [EmployeeTrainingController::class, 'action'])->name('employee.training.action');

    Route::get('/ui-preview/employee/training-saya', $placeholder('employee', 'Training Saya', 'Placeholder untuk halaman training saya.'))->name('preview.employee.training');
    Route::get('/ui-preview/employee/riwayat-training', $placeholder('employee', 'Riwayat Training', 'Placeholder untuk halaman riwayat training.'))->name('employee.history.index');
    Route::get('/ui-preview/employee/profil', $placeholder('employee', 'Profil', 'Placeholder untuk halaman profil karyawan.'))->name('employee.profile.index');
    Route::get('/ui-preview/employee/ubah-password', $placeholder('employee', 'Ubah Password', 'Placeholder untuk halaman ubah password karyawan.'))->name('employee.password.index');
});
