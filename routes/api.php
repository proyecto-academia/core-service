<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    'uses' => function () {
        return response()->json([
            'message' => 'Welcome to the core API',
            'status' => 200,
            'url' => request()->url(),
            'path' => request()->path(),
        ]);
    },
]);


Route::middleware('auth.remote')->group(function () {
    
});



// not found route
Route::fallback(function () {
    return response()->json([
        'message' => 'Not Found',
        'status' => 404,
        'url' => request()->url(),
        'path' => request()->path(),
    ], 404);
});
