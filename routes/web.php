<?php

use App\Http\Controllers\SanitizerController;

Route::get('/', [SanitizerController::class, 'index'])->name('sanitizer.index');
Route::post('/sanitize', [SanitizerController::class, 'sanitize'])->name('sanitizer.sanitize');
