<?php

use ArchiElite\UrlShortener\Http\Controllers\AnalyticsController;
use ArchiElite\UrlShortener\Http\Controllers\QrCodeController;
use ArchiElite\UrlShortener\Http\Controllers\UrlShortenerController;
use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'url-shortener', 'as' => 'url_shortener.'], function () {
        Route::resource('', UrlShortenerController::class)->parameters(['' => 'url-shortener']);
        Route::get('analytics/{url}', [AnalyticsController::class, 'show'])->name('analytics');

        Route::group(['prefix' => 'qr-code', 'as' => 'qr-code.'], function () {
            Route::get('{short_url}', [QrCodeController::class, 'show'])->name('show');
            Route::post('{short_url}/generate', [QrCodeController::class, 'generate'])->name('generate');
            Route::get('{short_url}/download', [QrCodeController::class, 'download'])->name('download');
            Route::get('{short_url}/image', [QrCodeController::class, 'image'])->name('image');
            Route::delete('{short_url}/cache', [QrCodeController::class, 'clearCache'])->name('clear-cache');
        });
    });
});

Route::group(['middleware' => ['web', 'core']], function () {
    Route::get('go/{url}', [AnalyticsController::class, 'view'])->name('url_shortener.go');
});
