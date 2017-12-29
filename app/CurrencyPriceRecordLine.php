<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyPriceRecordLine extends Model
{
    protected $fillable = [
        'currency_price_record_id',
        'currency_id',
        'base_currency_price',
        'prem_currency_price',
        'prem_amount',
        'prem_rate'
    ];



    // Relations
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }



}
