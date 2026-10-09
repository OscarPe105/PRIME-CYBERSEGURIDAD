<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrimeController;

Route::get('/', function () {
    return response()->file(public_path('prime.html'));
});

Route::prefix('api')->group(function() {
 Route::get('session',[PrimeController::class,'session']);
 Route::post('auth/login',[PrimeController::class,'login'])->middleware('throttle:10,1');
 Route::post('auth/mfa',[PrimeController::class,'verify'])->middleware('throttle:5,1');
 Route::post('auth/logout',[PrimeController::class,'logout']);
 Route::get('findings',[PrimeController::class,'index']);
 Route::post('imports',[PrimeController::class,'upload']);
 Route::post('findings/{id}/cti',[PrimeController::class,'enrich'])->middleware('throttle:20,1');
 Route::get('findings/{id}/history',[PrimeController::class,'history']);
 Route::post('findings/{id}/propose',[PrimeController::class,'propose']);
 Route::post('findings/{id}/evidence',[PrimeController::class,'evidence']);
 Route::post('findings/{id}/review',[PrimeController::class,'review']);
 Route::get('evidence/{id}',[PrimeController::class,'download']);
 Route::get('audit',[PrimeController::class,'auditLog']);
 Route::get('export',[PrimeController::class,'export']);
});
