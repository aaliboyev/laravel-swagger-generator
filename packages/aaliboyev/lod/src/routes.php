<?php


use Illuminate\Support\Facades\Route;
use Aaliboyev\Lod\Http\Controllers\OpenApiController;


Route::get('openapi.json', [OpenApiController::class, 'index']);

Route::get('docs', function () {
    return view('lod::swagger');
})->name('docs');
