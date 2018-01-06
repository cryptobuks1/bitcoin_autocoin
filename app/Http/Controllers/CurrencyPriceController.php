<?php

namespace App\Http\Controllers;

use App\Currency;
use App\CurrencyPriceRecord;
use Illuminate\Http\Request;

class CurrencyPriceController extends Controller
{


    public function index()
    {
        $premiumOnly = (bool)request('premiumOnly');
        $activeOnly = (bool)request('activeOnly');

        $currencies = $activeOnly?
            Currency
            ::active()
            ->orderBy('order')
            ->get():

            Currency
            ::orderBy('order')
            ->get();



        $records = CurrencyPriceRecord::with('currencyPriceRecordLines')
            ->orderBy('recorded_at', 'desc')
            ->paginate(240);



        $records->getCollection()->transform(function($record){
            $lines = [];
            foreach($record->currencyPriceRecordLines as $line){
                $lines[$line->currency->currency_code] = $line;
            }

            $record->lines = $lines;

            return $record;

        });



        return $premiumOnly?
            view('list-prem-only', compact('records', 'currencies')):
            view('list', compact('records', 'currencies'));
    }







}



