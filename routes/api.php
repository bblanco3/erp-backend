<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ProjectLifecycleController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\EstimateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Project routes
Route::get('/projects', [ProjectController::class, 'index']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);
Route::put('/projects/{project}', [ProjectController::class, 'update']);
Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

// Customer routes
Route::get('/customers', [CustomerController::class, 'index']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers/{id}', [CustomerController::class, 'show']);
Route::put('/customers/{id}', [CustomerController::class, 'update']);
Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

// Supplier routes
Route::get('/suppliers', [SupplierController::class, 'index']);
Route::post('/suppliers', [SupplierController::class, 'store']);
Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

// Calendar events
Route::get('/events', function () {
    $currentDate = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $dayAfter = date('Y-m-d', strtotime('+2 days'));
    
    return response()->json([
        [
            'id' => 1,
            'title' => 'Project Kickoff',
            'date' => $currentDate,
            'time' => '09:00',
            'description' => 'Initial meeting for new client project',
            'type' => 'meeting'
        ],
        [
            'id' => 2,
            'title' => 'Team Training',
            'date' => $currentDate,
            'time' => '14:00',
            'description' => 'React advanced patterns workshop',
            'type' => 'training'
        ],
        [
            'id' => 3,
            'title' => 'Client Review',
            'date' => $tomorrow,
            'time' => '11:00',
            'description' => 'Monthly progress review with client',
            'type' => 'review'
        ],
        [
            'id' => 4,
            'title' => 'Design Sprint',
            'date' => $tomorrow,
            'time' => '10:00',
            'description' => 'UI/UX design sprint session',
            'type' => 'workshop'
        ],
        [
            'id' => 5,
            'title' => 'Code Review',
            'date' => $dayAfter,
            'time' => '15:00',
            'description' => 'Team code review session',
            'type' => 'meeting'
        ],
        [
            'id' => 6,
            'title' => 'Window Installation',
            'date' => $currentDate,
            'time' => '10:00',
            'description' => 'Install new windows for client',
            'type' => 'installation'
        ],
        [
            'id' => 7,
            'title' => 'Service Call',
            'date' => $tomorrow,
            'time' => '13:00',
            'description' => 'Regular maintenance check',
            'type' => 'service'
        ],
        [
            'id' => 8,
            'title' => 'Quote Meeting',
            'date' => $dayAfter,
            'time' => '09:00',
            'description' => 'New project quote discussion',
            'type' => 'quote'
        ]
    ]);
});