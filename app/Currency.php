<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'currency_code',
        'currency_name'
    ];



    public static function findByCode($code)
    {
        return self::filterByCode($code)->first();
    }

    public function getBaseExchangeAttribute()
    {
        return $this->exchanges->base_exchange;
    }

    public function getPremExchangeAttribute()
    {
        return $this->exchanges->prem_exchange;
    }


    // Scope
    public function scopeFilterByCode($query, $code)
    {
        $query->where('currency_code', $code);
    }

    public function scopeActive($query)
    {
        $query->where('is_active', true);
    }




    // Relations
    public function exchanges()
    {
        return $this->hasOne(CurrencyExchange::class);
    }



}
