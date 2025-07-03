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
use App\Http\Controllers\ReportQcEquipmentController;
use App\Http\Controllers\ScaleController;
use App\Http\Controllers\ReportScaleController;
use App\Http\Controllers\ThermometerController;
use App\Http\Controllers\RoomEquipmentController;
use App\Http\Controllers\ReportReCleanlinessController;
use App\Http\Controllers\ReportRepairCleanlinessController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportConveyorCleanlinessController;
use App\Http\Controllers\ReportSolventController;
use App\Http\Controllers\ReportRmArrivalController;
use App\Http\Controllers\PremixController;
use App\Http\Controllers\ReportPremixController;
use App\Http\Controllers\ReportForeignObjectController;
use App\Http\Controllers\ReportMagnetTrapController;
use App\Http\Controllers\SharpToolController;
use App\Http\Controllers\ReportSharpToolController;
use App\Http\Controllers\ReportProductChangeController;
use App\Http\Controllers\ReportPreOperationController;
use App\Http\Controllers\ReportProductionNonconformityController;
use App\Http\Controllers\ReportChlorineResidueController;

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

    // GMP EMPLOYEE ROUTES
    Route::prefix('gmp-employee')
        ->name('gmp-employee.')
        ->controller(GmpController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->whereUuid('uuid')->name('edit');
            Route::put('/{uuid}', 'update')->whereUuid('uuid')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('{report}/detail/create', 'createDetail')->name('detail.create');
            Route::post('{report}/detail/store', 'storeDetail')->name('detail.store');
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
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
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

    // REPORT QC EQUIPMENT ROUTES
    Route::prefix('report-qc-equipment')
        ->name('report-qc-equipment.')
        ->controller(ReportQcEquipmentController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export', 'exportPdf')->name('export');
        });

    // SCALES MD ROUTES
    Route::prefix('scales')
        ->name('scales.')
        ->controller(ScaleController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
        });

    // REPORT SCALES ROUTES
    Route::prefix('report-scales')
        ->name('report-scales.')
        ->controller(ReportScaleController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index'); // daftar laporan
            Route::get('/create', 'create')->name('create'); // form create (isi + detail)
            Route::post('/', 'store')->name('store'); // simpan header + detail
            Route::get('/{uuid}/edit', 'edit')->name('edit'); // edit header
            Route::put('/{uuid}', 'update')->name('update'); // update header
            Route::delete('/{uuid}', 'destroy')->name('destroy'); // hapus laporan
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('export-pdf');
        });

    // THERMOMETER MD ROUTES
    Route::prefix('thermometers')
        ->name('thermometers.')
        ->controller(ThermometerController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
        });

    // VERKEBRUANG MD ROUTES
    Route::prefix('rooms')
        ->name('rooms.')
        ->controller(RoomEquipmentController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'storeRoom')->name('store');
            Route::delete('/{uuid}', 'destroyRoom')->name('destroy');
        });
    Route::prefix('equipments')
        ->name('equipments.')
        ->controller(RoomEquipmentController::class)
        ->group(function () {
            Route::post('/', 'storeEquipment')->name('store');
            Route::delete('/{uuid}', 'destroyEquipment')->name('destroy');
        });

    // REPORT RE ROUTES
    Route::prefix('report-re-cleanliness')
        ->name('report-re-cleanliness.')
        ->controller(ReportReCleanlinessController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('exportPdf');
        });

    // REPORT REPAIR CLEANLINESS ROUTES
    Route::prefix('repair-cleanliness')
        ->name('repair-cleanliness.')
        ->controller(ReportRepairCleanlinessController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::get('/{uuid}/add-detail', 'createDetail')->name('add-detail');
            Route::post('/store-detail', 'storeDetail')->name('store-detail');
            Route::get('/{uuid}/export', 'exportPdf')->name('export');
            Route::post('/{id}/approve', 'approve')->name('approve');
        });

    // MD PRODUCT
    Route::prefix('products')
        ->name('products.')
        ->controller(ProductController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
        });

    // REPORT CONVEYOR
    Route::prefix('report-conveyor-cleanliness')
        ->name('report-conveyor-cleanliness.')
        ->controller(ReportConveyorCleanlinessController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::get('/{uuid}/add-detail', 'addDetail')->name('add-detail');
            Route::post('/{uuid}/add-detail', 'storeDetail')->name('store-detail');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('export-pdf');
        });

    // REPORT SOLVENT
    Route::prefix('report-solvents')
        ->name('report-solvents.')
        ->controller(ReportSolventController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('export-pdf');
        });

    // REPORT RM ARRIVAL
    Route::prefix('report-rm-arrivals')
        ->name('report_rm_arrivals.')
        ->controller(ReportRmArrivalController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::get('/{uuid}/add-detail', 'addDetail')->name('add_detail');
            Route::post('/{uuid}/store-detail', 'storeDetail')->name('store_detail');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('export-pdf');
        });

    // MD PREMIX
    Route::prefix('premixes')->name('premixes.')->controller(PremixController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{uuid}/edit', 'edit')->name('edit');
        Route::put('/{uuid}', 'update')->name('update');
        Route::delete('/{uuid}', 'destroy')->name('destroy');
    });

    // REPORT PREMIX
    Route::prefix('report-premixes')->name('report-premixes.')->controller(ReportPremixController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::delete('/{uuid}', 'destroy')->name('destroy');
        Route::post('/{id}/approve', 'approve')->name('approve');
        Route::get('/{uuid}/export-pdf', 'exportPdf')->name('exportPdf');
    });

    // REPORT FOREIGN OBJECT
    Route::prefix('report-foreign-objects')
        ->name('report-foreign-objects.')
        ->controller(ReportForeignObjectController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}/add-detail', 'createDetail')->name('add-detail');
            Route::post('/{uuid}/add-detail', 'storeDetail')->name('store-detail');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('export-pdf');
        });

    // REPORT MAGNET TRAP
    Route::prefix('report-magnet-traps')
        ->name('report_magnet_traps.')
        ->controller(ReportMagnetTrapController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::delete('/{uuid}/destroy', 'destroy')->name('destroy');
            Route::get('/{uuid}/details/add', 'addDetail')->name('details.add');
            Route::post('/{uuid}/details/store', 'storeDetail')->name('details.store');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('exportPdf');
        });

    // SHARP TOOL MD
    Route::prefix('sharp-tools')->name('sharp_tools.')->controller(SharpToolController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{uuid}/edit', 'edit')->name('edit');
        Route::put('/{uuid}', 'update')->name('update');
        Route::delete('/{uuid}', 'destroy')->name('destroy');
    });

    // REPORT BENDA TAJAM
    Route::prefix('report-sharp-tools')->name('report_sharp_tools.')->controller(ReportSharpToolController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{uuid}/edit', 'edit')->name('edit');
        Route::put('/{uuid}', 'update')->name('update');
        Route::delete('/{uuid}', 'destroy')->name('destroy');
        Route::get('/{uuid}/add-detail', 'addDetail')->name('details.add');
        Route::post('/{uuid}/add-detail', 'storeDetail')->name('details.store');
        Route::post('/{id}/approve', 'approve')->name('approve');
        Route::get('/{uuid}/pdf', 'exportPdf')->name('exportPdf');
    });

    // REPORT
    Route::prefix('report-product-changes')->name('report_product_changes.')->controller(ReportProductChangeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::delete('/{uuid}', 'destroy')->name('destroy');
        Route::post('/{id}/approve', 'approve')->name('approve');
        Route::get('/export-pdf/{uuid}', 'exportPdf')->name('export-pdf');
    });

    Route::prefix('report-pre-operations')
        ->name('report_pre_operations.')
        ->controller(ReportPreOperationController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{report_pre_operation}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('export-pdf/{uuid}', 'exportPdf')->name('export-pdf');
        });

    Route::prefix('report-production-nonconformities')
        ->name('report_production_nonconformities.')
        ->controller(ReportProductionNonconformityController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/export-pdf/{uuid}', 'exportPdf')->name('export-pdf');
            Route::get('/{uuid}/add-detail', 'addDetail')
                ->name('add-detail');
            Route::post('/{uuid}/store-detail', 'storeDetail')->name('store-detail');
            Route::get('/export-pdf/{uuid}', 'exportPdf')->name('export-pdf');
        });

    Route::prefix('report-chlorine-residues')
        ->name('report_chlorine_residues.')
        ->controller(ReportChlorineResidueController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{uuid}', 'destroy')->name('destroy');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::get('/{uuid}/export-pdf', 'exportPdf')->name('export-pdf');
            Route::get('/{uuid}/edit', 'edit')->name('edit');
            Route::put('/{uuid}', 'update')->name('update');
        });
});