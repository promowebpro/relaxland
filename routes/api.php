<?php

use App\Http\Controllers\Api\GenplanApiController;
use Illuminate\Support\Facades\Route;

Route::get('/genplan', [GenplanApiController::class, 'overview'])->name('api.genplan.overview');
Route::get('/genplan/quarters/{quarter}', [GenplanApiController::class, 'quarter'])->name('api.genplan.quarters.show');
Route::get('/genplan/quarters/{quarter}/plots', [GenplanApiController::class, 'plots'])->name('api.genplan.quarters.plots');
Route::get('/genplan/infrastructure', [GenplanApiController::class, 'infrastructure'])->name('api.genplan.infrastructure');
Route::get('/genplan/surroundings', [GenplanApiController::class, 'surroundings'])->name('api.genplan.surroundings');
