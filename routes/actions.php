<?php

Route::post('/webhook', '\Mikemartin\Samcart\Http\Controllers\WebhookController@store')->name('webhook');