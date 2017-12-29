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



    // Scope
    public function scopeFilterByCode($query, $code)
    {
        $query->where('currency_code', $code);
    }

    public function scopeActive($query)
    {
        $query->where('is_active', true);
    }




}
