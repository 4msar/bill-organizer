<?php

use Illuminate\Mail\Markdown;
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

// API v1 routes
Route::prefix('v1')->group(base_path('routes/api/v1.php'));

Route::get('/', function () {
    return response()->json([
        'message' => "Welcome to the API!",
        'docs' => [
            'v1' => 'https://github.com/4msar/bill-organizer/blob/main/docs/api.md',
            'Postman Collection' => 'https://raw.githubusercontent.com/4msar/bill-organizer/refs/heads/main/docs/postman-collection.json',
        ],
    ]);
});
