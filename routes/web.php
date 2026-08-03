<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

// Fallback route untuk menyajikan file dari storage/app/public jika symlink belum aktif di server/cloud deployment
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }
    return response()->file($filePath);
})->where('path', '.*');

require __DIR__.'/auth.php';
