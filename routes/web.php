<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
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

    Route::get('/ui-preview/admin/karyawan', [EmployeeController::class, 'index'])->name('preview.admin.karyawan');
    Route::get('/ui-preview/admin/divisi', [DivisionController::class, 'index'])->name('preview.admin.divisi');
    Route::get('/ui-preview/admin/jabatan', [PositionController::class, 'index'])->name('preview.admin.jabatan');
    Route::get('/ui-preview/admin/daftar-training', $placeholder('admin', 'Daftar Training', 'Placeholder untuk halaman daftar training.'))->name('admin.training.index');
    Route::get('/ui-preview/admin/materi-training', $placeholder('admin', 'Materi Training', 'Placeholder untuk halaman materi training.'))->name('admin.materi.index');
    Route::get('/ui-preview/admin/soal-test', $placeholder('admin', 'Soal Test', 'Placeholder untuk halaman soal test.'))->name('admin.soal.index');
    Route::get('/ui-preview/admin/penugasan-training', $placeholder('admin', 'Penugasan Training', 'Placeholder untuk halaman penugasan training.'))->name('admin.penugasan.index');
    Route::get('/ui-preview/admin/jawaban-essay', $placeholder('admin', 'Jawaban Essay', 'Placeholder untuk halaman penilaian jawaban essay.'))->name('admin.jawaban-essay.index');
    Route::get('/ui-preview/admin/hasil-test', $placeholder('admin', 'Hasil Test', 'Placeholder untuk halaman hasil test.'))->name('admin.hasil-test.index');
    Route::get('/ui-preview/admin/progress-training', $placeholder('admin', 'Progress Training', 'Placeholder untuk halaman monitoring progress training.'))->name('admin.progress.index');
    Route::get('/ui-preview/admin/laporan', $placeholder('admin', 'Laporan', 'Placeholder untuk halaman laporan.'))->name('admin.laporan.index');
    Route::get('/ui-preview/admin/export-data', $placeholder('admin', 'Export Data', 'Placeholder untuk halaman export data.'))->name('admin.export.index');
    Route::get('/ui-preview/admin/profil', $placeholder('admin', 'Profil Admin', 'Placeholder untuk halaman profil admin.'))->name('admin.profile.index');
    Route::get('/ui-preview/admin/ubah-password', $placeholder('admin', 'Ubah Password', 'Placeholder untuk halaman ubah password admin.'))->name('admin.password.index');
});

Route::middleware(['auth', 'active', 'role:karyawan'])->group(function () use ($placeholder) {
    Route::view('/karyawan/dashboard', 'employee.dashboard')->name('employee.dashboard');
    Route::view('/ui-preview/employee', 'employee.dashboard')->name('preview.employee');

    Route::get('/ui-preview/employee/training-saya', $placeholder('employee', 'Training Saya', 'Placeholder untuk halaman training saya.'))->name('employee.training.index');
    Route::get('/ui-preview/employee/riwayat-training', $placeholder('employee', 'Riwayat Training', 'Placeholder untuk halaman riwayat training.'))->name('employee.history.index');
    Route::get('/ui-preview/employee/profil', $placeholder('employee', 'Profil', 'Placeholder untuk halaman profil karyawan.'))->name('employee.profile.index');
    Route::get('/ui-preview/employee/ubah-password', $placeholder('employee', 'Ubah Password', 'Placeholder untuk halaman ubah password karyawan.'))->name('employee.password.index');
});
