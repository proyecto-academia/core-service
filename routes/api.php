<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ClassModelController;
use App\Http\Controllers\PackController;

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
    // Inscripciones
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);

    // Compras
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);

    Route::apiResource('courses', CourseController::class);
    Route::apiResource('classes', ClassModelController::class);
    Route::apiResource('packs', PackController::class);
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
