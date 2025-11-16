<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/broadcasting/auth', function (Request $request) {
    try {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 403);
        }
        
        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken) {
            return response()->json(['message' => 'Invalid token'], 403);
        }
        
        $user = $personalAccessToken->tokenable;
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 403);
        }
        
        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');
        
        if (!$channelName || !$socketId) {
            return response()->json(['message' => 'Missing channel_name or socket_id'], 400);
        }
        
        preg_match('/^private-user\.(\d+)$/', $channelName, $matches);
        $channelUserId = isset($matches[1]) ? (int) $matches[1] : null;
        
        if ($channelUserId !== $user->id) {
            return response()->json(['message' => 'Unauthorized for this channel'], 403);
        }
        
        $pusherSecret = config('broadcasting.connections.pusher.secret');
        $pusherKey = config('broadcasting.connections.pusher.key');
        
        if (!$pusherSecret || !$pusherKey) {
            return response()->json(['message' => 'Broadcasting not configured'], 500);
        }
        
        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $pusherSecret, false);
        $auth = $pusherKey . ':' . $signature;
        
        return response()->json([
            'auth' => $auth,
        ]);
        
    } catch (\Exception $e) {
        Log::error('Broadcasting auth exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        return response()->json(['message' => 'Authentication failed'], 500);
    }
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/users/email/{email}', [UserController::class, 'getByEmail']);
});

