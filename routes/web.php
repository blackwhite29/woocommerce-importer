<?php

Route::group([
    'namespace' => 'Botble\WooCommerceImporter\Http\Controllers',
    'middleware' => ['web', 'core'],
], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => '/tools'], function () {
            Route::get('woocommerce-importer', [
                'as' => 'woocommerce-importer',
                'uses' => 'WooCommerceImporterController@index',
                'permission' => 'settings.options',
            ]);

            Route::post('woocommerce-importer', [
                'as' => 'woocommerce-importer.post',
                'uses' => 'WooCommerceImporterController@import',
                'permission' => 'settings.options',
            ]);
        });
    });
});
