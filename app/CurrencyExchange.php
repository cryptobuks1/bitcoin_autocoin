<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyExchange extends Model
{
    protected $fillable = [
        'currency_id',
        'base_exchange_id',
        'prem_exchange_id'
    ];
    
    // Relations
    public function base_exchange()
    {
        return $this->belongsTo(Exchange::class, 'base_exchange_id');
    }

    public function prem_exchange()
    {
        return $this->belongsTo(Exchange::class, 'prem_exchange_id');
    }
}
