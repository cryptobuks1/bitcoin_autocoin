<?php
namespace App\Exchanges\Clients;

use App\Exchanges\Http\Http;
use Illuminate\Database\Eloquent\Collection;

class Binance {

    protected $apiBase = 'https://api.binance.com/api/';
    //protected $apiKey =

    public function currencyIds()
    {
        return [
            'BTC' => 'BTCUSDT',
            'BCH' => 'BCCUSDT',
            'ETH' => 'ETHUSDT',
            'LTC' => 'LTCUSDT',
            'XRP' => ['XRPETH', 'ETHUSDT'],
            'DASH' => ['DASHETH', 'ETHUSDT'],
            'ETC' => ['ETCETH', 'ETHUSDT'],
            'XMR' => ['XMRETH', 'ETHUSDT'],
            'ZEC' => ['ZECETH', 'ETHUSDT'],
            'QTUM' => ['QTUMETH', 'ETHUSDT'],
            'BTG' => ['BTGETH', 'ETHUSDT'],
            'EOS' => ['EOSETH', 'ETHUSDT']
        ];
    }


    public function getPrices(Collection $currencies)
    {
        // make it to associative array
        $currencies = $currencies
            ->pluck('id', 'currency_code')
            ->map(function($id){
                return [
                    'id' => $id,
                    'currency_id' => null
                ];
            })->toArray();

        // add currencyIds
        foreach($this->currencyIds() as $currencyCode => $currencyID){
            if($currencies[$currencyCode]) {
                $currencies[$currencyCode]['currency_id'] = $currencyID;
            }
        }

        // make api call
        $version = 'v3';
        $url = $this->apiBase . $version . '/ticker/price';
        $res = null;

        try{
            $res = Http::request(Http::GET, $url);
        }
        catch (\Exception $e){
            // Error handling
            echo($e->getMessage());
            exit();
        }

        // data from API
        $data = [];
        $dataOriginal = json_decode($res->content, true);
        foreach($dataOriginal as $cur){
            $data[$cur['symbol']] = $cur;
        }

        // Finalize data. [id, currency_id, price]
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
                    $level1 = doubleval($data[$currency['currency_id'][0]]['price']);
                    $level2 = doubleval($data[$currency['currency_id'][1]]['price']);
                    $currency['price'] = $level1 * $level2;
                }
                else { // 1 step: string
                    $currency['price'] = doubleval($data[$currency['currency_id']]['price']);
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