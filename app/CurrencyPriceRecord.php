<?php

namespace App;

use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CurrencyPriceRecord extends Model
{
    protected $fillable = [
        'recorded_at'
    ];

    protected $dates = [
        'recorded_at'
    ];



    // Relations

    public function currencyPriceRecordLines()
    {
        return $this->hasMany(CurrencyPriceRecordLine::class);
    }
}
