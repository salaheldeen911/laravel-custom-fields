<?php

// use YourVendor\LaravelCustomFields\Http\Controllers\CustomFieldController;

use Salah\LaravelCustomFields\Http\Controllers\CustomFieldController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('custom-fields.routing.web.prefix', 'custom-fields'),
    'middleware' => config('custom-fields.routing.web.middleware', ['web']),
    'as' => 'custom-fields.',
], function () {
    Route::get('/', [CustomFieldController::class, 'index'])->name('index');
    Route::get('/create', [CustomFieldController::class, 'create'])->name('create');
    Route::post('/', [CustomFieldController::class, 'store'])->name('store');
    Route::post('/{id}/restore', [CustomFieldController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force', [CustomFieldController::class, 'forceDelete'])->name('force-delete');
    Route::get('/{customField}/edit', [CustomFieldController::class, 'edit'])->name('edit');
    Route::put('/{customField}', [CustomFieldController::class, 'update'])->name('update');
    Route::delete('/{customField}', [CustomFieldController::class, 'destroy'])->name('destroy');
});
