<?php

use App\Currency;
use App\CurrencyPriceRecord;

Route::get('/', 'CurrencyPriceController@index');



Route::get('/test', function(){

    dd(\App\CurrencyPriceRecord::addRecord());

    /*$currencies = Currency
        //::active()
        ::orderBy('order')
        ->get(['id', 'currency_code']);

    dd(CurrencyPriceRecord::getPricesFromBinance($currencies));*/

});


