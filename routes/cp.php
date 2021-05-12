<?php

use Illuminate\Support\Facades\Route;
use Michavie\Bearhub\Http\Controllers\CP\NotesController;

Route::prefix('bearhub')->name('bearhub.')->group(function () {
    Route::post('sync', [NotesController::class, 'sync'])->name('sync-notes');
});
