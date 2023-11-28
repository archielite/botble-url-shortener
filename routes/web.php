<?php

use ArchiElite\UrlShortener\Http\Controllers\AnalyticsController;
use ArchiElite\UrlShortener\Http\Controllers\UrlShortenerController;
use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['as' => 'url_shortener.'], function () {
        Route::prefix('url-shortener')->group(function () {
            Route::resource('', UrlShortenerController::class)->parameters(['' => 'url-shortener']);
            Route::get('analytics/{url}', [AnalyticsController::class, 'show'])->name('analytics');
        });

        Route::get('go/{url}', [AnalyticsController::class, 'view'])->name('go');
    });
});
