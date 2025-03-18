<?php

use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('products/{token}', [SiteController::class, 'getScrapingProduct'])->name('products.scraping');
Route::post('queue_status_completed/{siteId}', [SiteController::class, 'setQueueStatusCompleted']);
Route::get('get_products/{siteId}', [SiteController::class, 'getProducts']);
