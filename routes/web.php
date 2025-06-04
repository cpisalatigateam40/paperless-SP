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
            Route::get('/', 'index')->middleware('permission:user view')->name('index');
            Route::get('/create', 'create')->middleware('permission:user create')->name('create');
            Route::post('/', 'store')->middleware('permission:user create')->name('store');
            Route::get('/{uuid}/edit', 'edit')->middleware('permission:user edit')->name('edit');
            Route::put('/{uuid}', 'update')->middleware('permission:user edit')->name('update');
            Route::delete('/{uuid}', 'destroy')->middleware('permission:user delete')->name('destroy');
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
            Route::get('/', 'index')->middleware('permission:role view')->name('index');
            Route::get('/create', 'create')->middleware('permission:role create')->name('create');
            Route::post('/', 'store')->middleware('permission:role create')->name('store');
            Route::get('/{id}/edit', 'edit')->middleware('permission:role edit')->name('edit');
            Route::put('/{id}', 'update')->middleware('permission:role edit')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:role delete')->name('destroy');

            Route::get('/{role}/manage-access', 'manageAccess')->middleware('permission:role manage access')->name('manage-access');
            Route::post('/{role}/manage-access', 'updateAccess')->middleware('permission:role manage access')->name('manage-access.update');
        });

    // PERMISSION ROUTES
    Route::prefix('permissions')
        ->name('permissions.')
        ->controller(PermissionController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:permission view')->name('index');
            Route::get('/create', 'create')->middleware('permission:permission create')->name('create');
            Route::post('/', 'store')->middleware('permission:permission create')->name('store');
            Route::get('/{id}/edit', 'edit')->middleware('permission:permission edit')->name('edit');
            Route::put('/{permission}', 'update')->middleware('permission:permission edit')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:permission delete')->name('destroy');
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
            Route::get('/{id}/pdf', 'exportPdf')->name('export.pdf');
        });

    Route::prefix('process-area-cleanliness')
        ->name('process-area-cleanliness.')
        ->controller(ProcessAreaCleanlinessController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
        });

});