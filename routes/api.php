<?php

use Aaliboyev\Lod\Services\SchemaGenerator;
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


// Example route that retrieves a list of resources
Route::get('/products', function () {
    return response()->json(['message' => 'Retrieved all products'], 200);
});

// Example route that retrieves a single resource by ID
Route::get('/products/{id}', function ($id) {
    return response()->json(['message' => "Retrieved product with ID ${id}"], 200);
});

// Example route that creates a new resource
Route::post('/products', function (Request $request) {
    return response()->json(['message' => 'Created a new product'], 201);
});

// Example route that updates an existing resource
Route::put('/products/{id}', function ($id, Request $request) {
    return response()->json(['message' => "Updated product with ID ${id}"], 200);
});

// Example route that deletes an existing resource
Route::delete('/products/{id}', function ($id) {
    return response()->json(['message' => "Deleted product with ID ${id}"], 200);
});

// Example route that patches an existing resource
Route::patch('/products/{id}', function ($id, Request $request) {
    return response()->json(['message' => "Patched product with ID ${id}"], 200);
});


Route::group(['prefix' => 'organizations'], function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Retrieved all organizations']);
    });
    Route::get('/{id}', function ($id) {
        return response()->json(['message' => "Retrieved organization with ID ${id}"]);
    });
    Route::post('/', function (Request $request) {
        return response()->json(['message' => 'Created a new organization'], 201);
    });
    Route::put('/{id}', function ($id) {
        return response()->json(['message' => "Updated organization with ID ${id}"]);
    });
    Route::delete('/{id}', function ($id) {
        return response()->json(['message' => "Deleted organization with ID ${id}"]);
    });
});

Route::prefix('blogs')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Retrieved all blog posts']);
    });
    Route::get('/{id}', function ($id) {
        return response()->json(['message' => "Retrieved blog post with ID ${id}"]);
    });
    Route::post('/', function (Request $request) {
        return response()->json(['message' => 'Created a new blog post'], 201);
    });
    Route::put('/{id}', function ($id, Request $request) {
        return response()->json(['message' => "Updated blog post with ID ${id}"]);
    });
    Route::delete('/{id}', function ($id) {
        return response()->json(['message' => "Deleted blog post with ID ${id}"]);
    });
});


// Example route to authenticate a user
Route::post('/login', function (Request $request) {
    return response()->json(['message' => "User logged in"], 200);
});

// Example route to register a user
Route::post('/register', function (Request $request) {
    return response()->json(['message' => "User registered"], 201);
});

// Example route to return current user's profile
Route::get('/profile', function (Request $request) {
    return response()->json(['message' => "User profile"], 200);
});

// Example route to log out a user
Route::post('/logout', function (Request $request) {
    return response()->json(['message' => "User logged out"], 200);
});
