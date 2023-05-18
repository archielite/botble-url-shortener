<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'ArchiElite\ShortenerUrl\Http\Controllers', 'middleware' => 'web'], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'url-shortener', 'as' => 'url_shortener.'], function () {
            Route::resource('', 'UrlShortenerController')->parameters(['' => 'url_shortener']);

            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'ShortUrlController@deletes',
                'permission' => 'url_shortener.destroy',
            ]);

            Route::get('analytics/{url}', 'AnalyticsController@show')->name('analytics');
        });
    });

    Route::get('go/{url}', 'AnalyticsController@view')->name('url_shortener.go');
});
