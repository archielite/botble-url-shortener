<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'ArchiElite\ShortUrl\Http\Controllers', 'middleware' => 'web'], function () {
    Route::group(['prefix' => config('core.base.general.admin_dir'), 'middleware' => 'auth'], function () {
        Route::resource('short-urls', 'ShortUrlController', ['names' => 'short_url']);

        Route::group(['prefix' => 'short-urls'], function () {
            Route::delete('items/destroy', [
                'as' => 'short_url.deletes',
                'uses' => 'ShortUrlController@deletes',
                'permission' => 'short_url.destroy',
            ]);

            Route::get('/analytics/{url}', 'AnalyticsController@show')->name('short_url.analytics');
        });
    });

    Route::get('/go/{url}', 'AnalyticsController@view')->name('short_url.go');
});
