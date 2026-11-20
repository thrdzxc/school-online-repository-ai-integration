<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\students;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;




// GET all sample students
Route::get('/students', function () {
    return response()->json([
        [
            'id' => 1,
            'name' => 'Sales, Samuel Jr. C.',
            'course' => 'BSIT 3B'
        ],
        [
            'id' => 2,
            'name' => 'Samuel Sales',
            'course' => 'BSIT 3B'
        ]
    ]);
})->middleware(['auth:sanctum', 'active', 'role:admin']);


// POST a new student
Route::post('/students', function (Request $request) {
    $data = $request->validate([
        'name' => \App\Support\AccountValidation::name(),
        'course' => ['required', 'string', 'max:100'],
    ]);
    $student = students::create($data);

    return response()->json($student, 201);
})->middleware(['auth:sanctum', 'active', 'role:admin']);



// Register
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

// Login
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');



Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Employee Routes
    Route::middleware('role:admin')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index']);

    Route::post('/employees', [EmployeeController::class, 'store']);

    Route::get('/employees/{id}', [EmployeeController::class, 'show']);

    Route::put('/employees/{id}', [EmployeeController::class, 'update']);

    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    });

});
