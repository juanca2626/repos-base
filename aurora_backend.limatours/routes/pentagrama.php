<?php

use App\Http\Pentagrama\Controller\PentagramaController;


/*
|--------------------------------------------------------------------------
| Tourcms Routes
|--------------------------------------------------------------------------

/* Gestionar Servicios desde Pentagrama - Aurora */

// Enviar el alta de un servicio desde  la extensión Pentagrama a Aurora frontend
Route::get('list', [PentagramaController::class, 'index']);
Route::get('detail/{id}/service', [PentagramaController::class, 'show']);
Route::post('create/service', [PentagramaController::class, 'store']);
Route::post('update/{id}/service', [PentagramaController::class, 'update']);
