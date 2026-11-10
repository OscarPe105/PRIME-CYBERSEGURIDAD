<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\TreatmentController;
use App\Http\Middleware\RequireVerifiedRole;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->file(public_path('prime.html')));

Route::prefix('api')->group(function () {
    Route::get('session', [AuthController::class, 'session']);
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('auth/mfa', [AuthController::class, 'verify'])->middleware('throttle:5,1');

    Route::middleware(RequireVerifiedRole::class)->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('findings', [FindingController::class, 'index']);
        Route::get('findings/{id}/history', [FindingController::class, 'history']);
        Route::get('evidence/{id}', [EvidenceController::class, 'download']);
        Route::get('export', [FindingController::class, 'export']);
        Route::get('queue/archives', [QueueController::class, 'index']);
        Route::get('queue/archives/{id}', [QueueController::class, 'show']);
    });

    Route::middleware(RequireVerifiedRole::class.':analista,administrador')->group(function () {
        Route::post('imports', [ImportController::class, 'upload']);
        Route::post('queue/clear', [QueueController::class, 'clear']);
        Route::post('findings/{id}/cti', [FindingController::class, 'enrich'])->middleware('throttle:20,1');
        Route::post('findings/{id}/propose', [TreatmentController::class, 'propose']);
        Route::post('findings/{id}/evidence', [EvidenceController::class, 'evidence']);
    });

    Route::post('findings/{id}/review', [TreatmentController::class, 'review'])->middleware(RequireVerifiedRole::class.':administrador');
    Route::get('audit', [AuditController::class, 'auditLog'])->middleware(RequireVerifiedRole::class.':administrador,auditor');
});
