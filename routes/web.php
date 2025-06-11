<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\StorageRmCleanlinessController;
use App\Http\Controllers\ProcessAreaCleanlinessController;
use App\Http\Controllers\GmpController;
use App\Http\Controllers\FragileItemController;
use App\Http\Controllers\ReportFragileItemController;
use App\Http\Controllers\QcEquipmentController;
use App\Models\QcEquipment;

Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });

    // USER ROUTES
    Route::prefix('users')
        ->name('users.')
        ->controller(UserController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
        });

    // USER ROUTES
    Route::prefix('area')
        ->name('areas.')
        ->controller(AreaController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
        });

    // ROLE ROUTES
    Route::prefix('roles')
        ->name('roles.')
        ->controller(RoleController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');

            Route::get('/{role}/manage-access', 'manageAccess')->name('manage-access');
            Route::post('/{role}/manage-access', 'updateAccess')->name('manage-access.update');
        });

    // PERMISSION ROUTES
    Route::prefix('permissions')
        ->name('permissions.')
        ->controller(PermissionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{permission}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    // SECTION ROUTES
    Route::prefix('section')
        ->name('sections.')
        ->controller(SectionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
        });

    // RAW MATERIAL ROUTES
    Route::prefix('raw-material')
        ->name('raw-materials.')
        ->controller(RawMaterialController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
        });

    // STORAGE RM ROUTES
    Route::prefix('storage-rm-cleanliness')
        ->name('cleanliness.')
        ->controller(StorageRmCleanlinessController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/create', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{report}/add-detail', 'createDetail')->name('detail.create');
            Route::post('/{report}/add-detail', 'storeDetail')->name('detail.store');
            Route::get('/{uuid}/pdf', 'exportPdf')->whereUuid('uuid')->name('export.pdf');
        });

    // PROCESS AREA ROUTES
    Route::prefix('process-area-cleanliness')
        ->name('process-area-cleanliness.')
        ->controller(ProcessAreaCleanlinessController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/create', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{report}/add-detail', 'createDetail')->name('detail.create');
            Route::post('/{report}/add-detail', 'storeDetail')->name('detail.store');
            Route::get('/{uuid}/pdf', 'exportPdf')->whereUuid('uuid')->name('export.pdf');
        });

    // PROCESS AREA ROUTES
    Route::prefix('gmp-employee')
        ->name('gmp-employee.')
        ->controller(GmpController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('{report}/detail/create', 'createDetail')->name('detail.create');
            Route::post('{report}/detail/store', 'storeDetail')->name('detail.store');
            Route::get('{report}/sanitation-detail/create', 'createSanitationDetail')->name('sanitation-detail.create');
            Route::post('{report}/sanitation-detail', 'storeSanitationDetail')->name('sanitation-detail.store');
            Route::get('/{uuid}/pdf', 'exportPdf')->whereUuid('uuid')->name('export.pdf');
            Route::post('/{id}/approve', 'approve')->name('approve');
        });

    // FRAGILE ITEM MD ROUTES
    Route::prefix('fragile-item')
        ->name('fragile-item.')
        ->controller(FragileItemController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{fragileItem}/edit', 'edit')->name('edit');
            Route::put('/{fragileItem}', 'update')->name('update');
            Route::delete('/{fragileItem}', 'destroy')->name('destroy');
        });

    // REPORT FRAGILE ITEM MD ROUTES
    Route::prefix('report-fragile-item')
        ->name('report-fragile-item.')
        ->controller(ReportFragileItemController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export', 'exportPdf')->name('export');
        });

    // QC EQUIPMENT ROUTES
    Route::prefix('qc-equipment')
        ->name('qc-equipment.')
        ->controller(QcEquipmentController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{qcEquipment}/edit', 'edit')->name('edit');
            Route::put('/{qcEquipment}', 'update')->name('update');
            Route::delete('/{qcEquipment}', 'destroy')->name('destroy');
        });
});