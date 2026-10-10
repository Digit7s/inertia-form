<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response('ok'));
Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
Route::post('/items', [ItemController::class, 'store'])->name('items.store');
Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
Route::match(['put', 'patch'], '/items/{item}', [ItemController::class, 'update'])->name('items.update');
