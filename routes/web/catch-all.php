<?php

use App\Http\Controllers\GuideThreadController;
use Illuminate\Support\Facades\Route;

Route::name('category.')->group(function () {
    Route::get('/{slug?}', [GuideThreadController::class, 'categoryIndex'])->name('thread');
});
