<?php 

use App\Http\Controllers\Api\ApiController;

Route::post('/user-sync', [ApiController::class, 'syncUser']);
Route::post('/user-desync', [ApiController::class, 'desyncUser']);

Route::post('/plant-sync', [ApiController::class, 'syncPlant']);