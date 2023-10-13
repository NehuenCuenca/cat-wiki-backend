<?php

use App\Http\Controllers\BreedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Breed ROUTE'S
Route::get('breeds', [BreedController::class, 'getAllBreeds']);
Route::get('breed/{breed_id}', [BreedController::class, 'getBreed']);