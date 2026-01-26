<?php

use Salah\LaravelCustomFields\Http\Controllers\CustomFieldApiController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('custom-fields.routing.api.prefix', 'api/custom-fields'),
    'middleware' => config('custom-fields.routing.api.middleware', ['api']),
    'as' => 'custom-fields.api.',
], function () {
    Route::get('/', [CustomFieldApiController::class, 'index'])->name('index');
    Route::get('/models-and-types', [CustomFieldApiController::class, 'modelsAndTypes'])->name('models-and-types');
    Route::post('/', [CustomFieldApiController::class, 'store'])->name('store');
    Route::post('/{id}/restore', [CustomFieldApiController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force', [CustomFieldApiController::class, 'forceDestroy'])->name('forceDestroy');
    Route::get('/{customField}', [CustomFieldApiController::class, 'show'])->name('show');
    Route::put('/{customField}', [CustomFieldApiController::class, 'update'])->name('update');
    Route::delete('/{customField}', [CustomFieldApiController::class, 'destroy'])->name('destroy');
});
