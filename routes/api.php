<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalConsignadoController;


Route::prefix('robot')->group(function () {
    Route::post('portal_consignado', [PortalConsignadoController::class, 'Index']);
});
