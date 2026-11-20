<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ChecklistResultController;
use App\Http\Controllers\ChecklistTargetController;
use Illuminate\Support\Facades\Route;

// C
Route::controller(ChecklistController::class)->group(function () {
    Route::get('checklists', 'index')->middleware('auth.access');
    Route::get('checklists/{id}', 'show')->middleware('auth.access');
    Route::get('checklists-get-code', 'getCode')->middleware('auth.access');
    Route::get('checklists-get-by-code', 'getByCode')->middleware('auth.access');
    Route::get('datatables/checklists', 'datatables')->middleware('auth.access');
    Route::post('checklists', 'store')->middleware('auth.access');
    Route::put('checklists/{id}', 'update')->middleware('auth.access');
    Route::put('checklists/{id}/restore', 'restore')->middleware('auth.access');
    Route::delete('checklists/{id}', 'destroy')->middleware('auth.access');
});

Route::controller(ChecklistResultController::class)->group(function () {
    Route::get('checklist_results', 'index')->middleware('auth.access');
    Route::get('checklist_results/{id}', 'show')->middleware('auth.access');
    Route::get('datatables/checklist_results', 'datatables')->middleware('auth.access');
    Route::post('checklist_results', 'store')->middleware('auth.access');
    Route::put('checklist_results/{id}', 'update')->middleware('auth.access');
    Route::put('checklist_results/{id}/restore', 'restore')->middleware('auth.access');
    Route::delete('checklist_results/{id}', 'destroy')->middleware('auth.access');
});

Route::controller(ChecklistTargetController::class)->group(function () {
    Route::get('checklist_targets', 'index')->middleware('auth.access');
    Route::get('checklist_targets/{id}', 'show')->middleware('auth.access');
    Route::get('checklist_targets-get-code', 'getCode')->middleware('auth.access');
    Route::get('checklist_targets-get-by-code', 'getByCode')->middleware('auth.access');
    Route::get('datatables/checklist_targets', 'datatables')->middleware('auth.access');
    Route::post('checklist_targets', 'store')->middleware('auth.access');
    Route::put('checklist_targets/{id}', 'update')->middleware('auth.access');
    Route::put('checklist_targets/{id}/restore', 'restore')->middleware('auth.access');
    Route::delete('checklist_targets/{id}', 'destroy')->middleware('auth.access');
});
