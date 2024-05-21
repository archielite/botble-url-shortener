<?php

use ArchiElite\UrlShortener\Http\Controllers\AnalyticsController;
use ArchiElite\UrlShortener\Http\Controllers\UrlShortenerController;
use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'url-shortener', 'as' => 'url_shortener.'], function () {
        Route::resource('', UrlShortenerController::class)->parameters(['' => 'url-shortener']);
        Route::get('analytics/{url}', [AnalyticsController::class, 'show'])->name('analytics');
    });
});

Route::group(['middleware' => ['web', 'core']], function () {
    Route::get('go/{url}', [AnalyticsController::class, 'view'])->name('url_shortener.go');
});
