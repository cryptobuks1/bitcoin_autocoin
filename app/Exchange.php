<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    protected $fillable = [
        'exchange_name',
        'exchange_url',
        'exchange_base'
    ];


    public static function findByName($name)
    {
        return self::filterByName($name)->first();
    }


    public function scopeFilterByName($query, $name)
    {
        $query->where('exchange_name', $name);
    }
}
