<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BreedController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SheepController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // -------------------------------------------------------
    // PUBLIC
    // -------------------------------------------------------
    Route::get('breeds',                              [BreedController::class, 'index']);
    Route::get('sheep',                               [SheepController::class, 'index']);
    Route::get('sheep/{sheep}',                       [SheepController::class, 'show']);
    Route::get('sheep/{sheep}/weight-history',        [SheepController::class, 'weightHistory']);
    Route::get('sheep/{sheep}/vaccinations',          [SheepController::class, 'vaccinations']);
    Route::middleware('throttle:3,1')->post('orders',                   [OrderController::class, 'store']);
    Route::middleware('throttle:20,1')->get('orders/track/{orderNumber}', [OrderController::class, 'track']);

    Route::get('stats', function () {
        return response()->json([
            'available_count' => \App\Models\Sheep::where('status', 'available')->where('is_active', true)->count(),
            'breeds_count'    => \App\Models\Breed::where('is_active', true)->count(),
            'sold_count'      => \App\Models\Sheep::where('status', 'sold')->count(),
        ]);
    });

    // -------------------------------------------------------
    // AUTH ADMIN
    // -------------------------------------------------------
    Route::middleware('throttle:5,1')->post('admin/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('admin/logout', [AuthController::class, 'logout']);
        Route::get('admin/me',      [AuthController::class, 'me']);
    });

    // -------------------------------------------------------
    // ADMIN (authentifié + rôle admin)
    // -------------------------------------------------------
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

        // Dashboard
        Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

        // Moutons
        Route::apiResource('sheep', \App\Http\Controllers\Admin\SheepController::class);
        Route::post('sheep/{sheep}/photos',              [\App\Http\Controllers\Admin\SheepPhotoController::class, 'store']);
        Route::delete('sheep/{sheep}/photos/{photo}',    [\App\Http\Controllers\Admin\SheepPhotoController::class, 'destroy']);
        Route::post('sheep/{sheep}/vaccinations',        [\App\Http\Controllers\Admin\VaccinationController::class, 'store']);
        Route::post('sheep/{sheep}/weight',              [\App\Http\Controllers\Admin\WeightController::class, 'store']);

        // Commandes
        Route::get('orders',                             [\App\Http\Controllers\Admin\OrderController::class, 'index']);
        Route::get('orders/{order}',                     [\App\Http\Controllers\Admin\OrderController::class, 'show']);
        Route::patch('orders/{order}/confirm',           [\App\Http\Controllers\Admin\OrderController::class, 'confirm']);
        Route::patch('orders/{order}/cancel',            [\App\Http\Controllers\Admin\OrderController::class, 'cancel']);
        Route::patch('orders/{order}/deliver',           [\App\Http\Controllers\Admin\OrderController::class, 'deliver']);
        Route::patch('orders/{order}/note', function(\Illuminate\Http\Request $req, \App\Models\Order $order) {
            $order->update(['admin_notes' => $req->admin_notes]);
            return response()->json(['message' => 'Note sauvegardée.']);
        });
    });

});
