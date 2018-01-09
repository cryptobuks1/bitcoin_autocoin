<?php
namespace App\Exchanges\Clients;

use App\Exchanges\Http\Http;
use Illuminate\Database\Eloquent\Collection;

class Kraken {

    protected $apiBase = 'https://api.kraken.com/';


    public function currencyIds()
    {
        return [
            'BTC' => 'XXBTZUSD',
            'BCH' => 'BCHUSD',
            'ETH' => 'XETHZUSD',
            'LTC' => 'XLTCZUSD',
            'XRP' => 'XXRPZUSD',
            'DASH' => 'DASHUSD',
            'ETC' => 'XETCZUSD',
            'XMR' => 'XXMRZUSD',
            'ZEC' => 'XZECZUSD',
        ];
    }

    public function getPairs()
    {
        $version = '0';
        $url = $this->apiBase . $version . '/public/AssetPairs';

        $res = null;

        try{
            $res = Http::request(Http::GET, $url);
        }
        catch(\Exception $e){
            // Error handling
            echo($e->getMessage());
            exit();
        }

        $dataOriginal = json_decode($res->content, true);
        $data = $dataOriginal['result'];

        return $data;
    }



    public function getPrices(Collection $currencies)
    {
        // make it to associative array
        $currencies = $currencies
            ->pluck('id', 'currency_code')
            ->map(function($id){
                return [
                    'id' => $id,
                    'currency_id' => null,
                ];
            })->toArray();

        // add currencyIds
        foreach($this->currencyIds() as $currencyCode => $currencyID){
            if($currencies[$currencyCode]) {
                $currencies[$currencyCode]['currency_id'] = $currencyID;
            }
        }

        // make api call
        $pairs = $this->getPairs();
        $ids = [];
        foreach($pairs as $key => $value){
            $ids[] = $key;
        }
        $ids = implode(',', $ids);

        $version = '0';
        $url = $this->apiBase . $version . '/public/Ticker?pair=' . $ids;
        $res = null;

        try{
            $res = Http::request(Http::GET, $url);
        }
        catch(\Exception $e){
            // Error handling
            echo($e->getMessage());
            exit();
        }

        // data from API
        $dataOriginal = json_decode($res->content, true);
        $data = $dataOriginal['result'];


        // Finalize data [id, currency_id, price]
        $currencies = array_map(function($currency) use($data){
            $dataAvailable = true;
            if(is_array($currency['currency_id'])){
                foreach($currency['currency_id'] as $i){
                    if(!isset($data[$i])){
                        $dataAvailable = false;
                    }
                }
            } else {
                $dataAvailable = isset($data[$currency['currency_id']]);
            }

            if($dataAvailable){
                if(is_array($currency['currency_id'])){ // 2 step
                    $level1 = doubleval($data[$currency['currency_id'][0]]['c'][0]);
                    $level2 = doubleval($data[$currency['currency_id'][1]]['c'][0]);
                    $currency['price'] = $level1 * $level2;
                }
                else { // 1 step: string
                    $currency['price'] = doubleval($data[$currency['currency_id']]['c'][0]);
                }

            }
            else {
                $currency['price'] = null;
            }

            return $currency;

        }, $currencies);


        return $currencies;

    }


}