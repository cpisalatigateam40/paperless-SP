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

Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });

    // USER ROUTES
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:user view')->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:user create')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:user create')->name('users.store');
    Route::get('/users/{uuid}/edit', [UserController::class, 'edit'])->middleware('permission:user edit')->name('users.edit');
    Route::put('/users/{uuid}', [UserController::class, 'update'])->middleware('permission:user edit')->name('users.update');
    Route::delete('/users/{uuid}', [UserController::class, 'destroy'])->middleware('permission:user delete')->name('users.destroy');

    Route::get('/area', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/area/create', [AreaController::class, 'create'])->name('areas.create');
    Route::post('/area', [AreaController::class, 'store'])->name('areas.store');
    Route::get('/area/{uuid}/edit', [AreaController::class, 'edit'])->name('areas.edit');
    Route::put('/area/{uuid}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('/area/{uuid}', [AreaController::class, 'destroy'])->name('areas.destroy');

    // ROLE ROUTES
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:role view')->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->middleware('permission:role create')->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:role create')->name('roles.store');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->middleware('permission:role edit')->name('roles.edit');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:role edit')->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:role delete')->name('roles.destroy');

    Route::get('/roles/{role}/manage-access', [RoleController::class, 'manageAccess'])->middleware('permission:role manage access')->name('roles.manage-access');
    Route::post('/roles/{role}/manage-access', [RoleController::class, 'updateAccess'])->middleware('permission:role manage access')->name('roles.manage-access.update');

    // PERMISSION ROUTES
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permission view')->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->middleware('permission:permission create')->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:permission create')->name('permissions.store');
    Route::get('/permissions/{id}/edit', [PermissionController::class, 'edit'])->middleware('permission:permission edit')->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:permission edit')->name('permissions.update');
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:permission delete')->name('permissions.destroy');

    // SECTION ROUTES
    Route::prefix('section')->name('sections.')->group(function () {
        Route::get('/', [SectionController::class, 'index'])->name('index');
        Route::get('/create', [SectionController::class, 'create'])->name('create');
        Route::post('/', [SectionController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [SectionController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [SectionController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [SectionController::class, 'destroy'])->name('destroy');
    });

    // RAW MATERIAL ROUTES
    Route::prefix('raw-material')->name('raw-materials.')->group(function () {
        Route::get('/', [RawMaterialController::class, 'index'])->name('index');
        Route::get('/create', [RawMaterialController::class, 'create'])->name('create');
        Route::post('/', [RawMaterialController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [RawMaterialController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [RawMaterialController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [RawMaterialController::class, 'destroy'])->name('destroy');
    });

    Route::get('/cleanliness-form', [StorageRmCleanlinessController::class, 'create'])->name('cleanliness.create');
    Route::post('/cleanliness-form', [StorageRmCleanlinessController::class, 'store'])->name('cleanliness.store');

});