<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ClassModelController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\PaymentController;

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

Route::get('/courses/latest', [CourseController::class, 'getLatestCourses']);
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

Route::get('/packs', [PackController::class, 'index']);
Route::get('/packs/{id}', [PackController::class, 'show']);


Route::middleware('auth.remote')->group(function () {
    // Inscripciones
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::get('/enrollments/user', [EnrollmentController::class, 'getUserEnrollments']);
    Route::get('/enrollments/courses', [EnrollmentController::class, 'getUserCourses']);

    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);



    // Compras
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::get('/purchases/user', [PurchaseController::class, 'getUserPurchases']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);

    Route::post('/create-payment-intent', [PaymentController::class, 'create']);
    Route::post('/confirm-purchase', [PaymentController::class, 'confirm']);

    Route::apiResource('courses', CourseController::class)->except(['index', 'show']);
    Route::get('/courses/prices/min', [CourseController::class, 'getCoursesMinPrice']);
    Route::get('/courses/prices/max', [CourseController::class, 'getCoursesMaxPrice']);

    Route::apiResource('packs', PackController::class)->except(['index', 'show']);
    Route::get('/packs/{id}/courses', [PackController::class, 'getPackCourses']);

});

Route::middleware(['auth.remote', 'check.enrolled'])->group(function () {
    Route::apiResource('classes', ClassModelController::class)->except(['store', 'update', 'destroy']);
});

Route::middleware(['auth.remote', 'auth.role:admin,teacher'])->group(function () {
    Route::apiResource('classes', ClassModelController::class)->except(['index', 'show']);
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
