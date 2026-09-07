<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\BastDocumentController;
use App\Http\Controllers\Api\V1\PreventiveScheduleController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\UserController;

/*
|--------------------------------------------------------------------------
| SIPARAS API Routes — Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // 1. Modul Autentikasi & Profil Pengguna
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('auth.me');
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        });
    });

    // Rute yang membutuhkan otentikasi Sanctum
    Route::middleware('auth:sanctum')->group(function () {

        // 2. Modul Pengelolaan Master Data
        Route::prefix('master')->group(function () {
            // Units
            Route::get('units', [MasterDataController::class, 'getUnits']);
            Route::post('units', [MasterDataController::class, 'storeUnit'])
                ->middleware('role:superadmin,admin_sarpras,kd_sarpras');
            Route::put('units/{id}', [MasterDataController::class, 'updateUnit'])
                ->middleware('role:superadmin,admin_sarpras,kd_sarpras');
            Route::delete('units/{id}', [MasterDataController::class, 'deleteUnit'])
                ->middleware('role:superadmin,admin_sarpras');

            Route::get('units/{id}/rooms', [MasterDataController::class, 'getRoomsByUnit']);
            Route::post('rooms', [MasterDataController::class, 'addRoom'])
                ->middleware('role:superadmin,admin_sarpras,asp_wilayah');

            // Vendors
            Route::get('vendors', [MasterDataController::class, 'getVendors']);
            Route::get('vendors/{id}/history', [MasterDataController::class, 'getVendorHistory']);
            Route::post('vendors', [MasterDataController::class, 'addVendor'])
                ->middleware('role:superadmin,admin_sarpras,kd_sarpras');
            Route::put('vendors/{id}', [MasterDataController::class, 'updateVendor'])
                ->middleware('role:superadmin,admin_sarpras,kd_sarpras');
            Route::post('vendors/{id}/toggle-active', [MasterDataController::class, 'toggleVendorActive'])
                ->middleware('role:superadmin,admin_sarpras,kd_sarpras');
            Route::delete('vendors/{id}', [MasterDataController::class, 'deleteVendor'])
                ->middleware('role:superadmin,admin_sarpras');

            // Signatories
            Route::get('signatories', [MasterDataController::class, 'getSignatories'])
                ->middleware('role:admin_sarpras,kd_sarpras');
            Route::put('signatories/{id}', [MasterDataController::class, 'updateSignatory'])
                ->middleware('role:admin_sarpras');
        });

        // 3. Modul Transaksi Tiket 5 Prosedur
        Route::prefix('tickets')->group(function () {
            Route::get('/', [TicketController::class, 'index']);
            Route::get('/{id}', [TicketController::class, 'show']);
            
            Route::post('/', [TicketController::class, 'store'])
                ->middleware('role:superadmin,kepsek,dept_ygpkb,asp_wilayah,kd_sarpras');
            
            Route::post('/{id}/photos', [TicketController::class, 'uploadPhotos']);

            Route::get('/{id}/pdf', [TicketController::class, 'downloadPdf']);

            
            Route::put('/{id}/revision', [TicketController::class, 'submitRevision'])
                ->middleware('role:kepsek,dept_ygpkb,asp_wilayah');
            
            // 4. Modul Alur Persetujuan Tiket (Approval Engine)
            Route::post('/{id}/review-asp', [TicketController::class, 'reviewAsp'])
                ->middleware('role:asp_wilayah');
            
            Route::post('/{id}/approval-kd', [TicketController::class, 'approvalKd'])
                ->middleware('role:kd_sarpras');
        });

        // 5. Modul Mesin Purchase Order / PO Engine
        Route::prefix('purchase-orders')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index']);
            
            Route::post('/draft', [PurchaseOrderController::class, 'storeDraft'])
                ->middleware('role:asp_wilayah,admin_sarpras,kd_sarpras');
            
            Route::post('/{id}/issue-number', [PurchaseOrderController::class, 'issueNumber'])
                ->middleware('role:admin_sarpras,kd_sarpras');
                
            Route::get('/{id}/pdf', [PurchaseOrderController::class, 'downloadPdf']);
            
            Route::post('/{id}/upload-scan', [PurchaseOrderController::class, 'uploadScan'])
                ->middleware('role:admin_sarpras');
        });

        // 6. Modul Mesin Berita Acara / BAST Engine
        Route::prefix('bast-documents')->group(function () {
            Route::get('/{ticket_id}', [BastDocumentController::class, 'show']);
            
            Route::post('/generate', [BastDocumentController::class, 'generate'])
                ->middleware('role:asp_wilayah,admin_sarpras,kd_sarpras');
                
            Route::get('/{ticket_id}/pdf', [BastDocumentController::class, 'downloadPdf']);
            
            Route::post('/{ticket_id}/upload-scan', [BastDocumentController::class, 'uploadScan'])
                ->middleware('role:admin_sarpras');
        });

        // 7. Modul Pemeliharaan Preventif
        Route::prefix('preventive-schedules')->group(function () {
            Route::get('/', [PreventiveScheduleController::class, 'index']);
            
            Route::post('/', [PreventiveScheduleController::class, 'store'])
                ->middleware('role:admin_sarpras,kd_sarpras');
                
            Route::post('/{id}/trigger-service', [PreventiveScheduleController::class, 'triggerService'])
                ->middleware('role:asp_wilayah,admin_sarpras');
        });

        // 8. Modul Rekam Jejak Audit & Ekspor Laporan
        Route::prefix('audit-logs')->group(function () {
            Route::get('/timeline/{record_id}', [ReportController::class, 'timeline']);
            Route::get('/system', [ReportController::class, 'auditSystem'])
                ->middleware('role:admin_sarpras,kd_sarpras,koordinator_utama');
        });

        Route::prefix('reports')->group(function () {
            Route::get('/recap', [ReportController::class, 'recap'])
                ->middleware('role:kd_sarpras,koordinator_ski,koordinator_utama');
                
            Route::get('/export-excel', [ReportController::class, 'exportExcel'])
                ->middleware('role:kd_sarpras,koordinator_ski,koordinator_utama');
                
            Route::get('/export-pdf', [ReportController::class, 'exportPdf'])
                ->middleware('role:superadmin,kd_sarpras,koordinator_ski,koordinator_utama');
        });

        // 9. Modul Manajemen Pengguna & Access Control
        Route::prefix('users')->group(function () {
            Route::get('/',                [UserController::class, 'index']);
            Route::get('/available-roles', [UserController::class, 'availableRoles']);
            Route::get('/{id}',            [UserController::class, 'show']);

            Route::post('/',               [UserController::class, 'store'])
                ->middleware('role:superadmin,kd_sarpras,admin_sarpras');

            Route::put('/{id}',            [UserController::class, 'update'])
                ->middleware('role:superadmin,kd_sarpras,admin_sarpras');

            Route::post('/{id}/change-password', [UserController::class, 'changePassword']);

            Route::post('/{id}/toggle-active',   [UserController::class, 'toggleActive'])
                ->middleware('role:superadmin,kd_sarpras,admin_sarpras');
        });

    });
});
