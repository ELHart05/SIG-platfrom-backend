<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/admin/reports', [ReportController::class, 'index']); //adminReports
Route::get('/admin/reports/{id}', [ReportController::class, 'show']);
Route::patch('/admin/reports/{id}/', [ReportController::class, 'updateReport']);
Route::get('/client/reports/{id}', [ReportController::class, 'getClientReports']); //id is client user id
Route::post('/reports', [ReportController::class, 'store']);
Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
Route::put('/reports/{id}/', [ReportController::class, 'update']);
Route::get('/statistics', [ReportController::class, 'statistics']);
