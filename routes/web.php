<?php

Route::get('/', 'CurrencyPriceController@index');



Route::get('/test', function(){

    \App\CurrencyPriceRecord::addRecord();

});
