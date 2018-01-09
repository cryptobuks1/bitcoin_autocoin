<?php

use App\Currency;
use App\CurrencyPriceRecord;

Route::get('/price', 'CurrencyPriceController@index');






// For test only
Route::get('/price/add', 'CurrencyPriceController@addRecord');


Route::get('/test', function(){


    //dd(\App\CurrencyPriceRecord::addRecord());

    /*$currencies = Currency
        //::active()
        ::orderBy('order')
        ->get(['id', 'currency_code']);

    dd(CurrencyPriceRecord::getPricesFromBinance($currencies));*/

});


