<?php
namespace App\Exchanges\Clients;


use App\Exchanges\Http\Http;
use Illuminate\Database\Eloquent\Collection;

class Bithumb {

    protected $apiBase = 'https://api.bithumb.com';


    public function currencyIds()
    {
        return [
            'BTC' => 'BTC',
            'BCH' => 'BCH',
            'ETH' => 'ETH',
            'LTC' => 'LTC',
            'XRP' => 'XRP',
            'DASH' => 'DASH',
            'ETC' => 'ETC',
            'XMR' => 'XMR',
            'ZEC' => 'ZEC',
            'QTUM' => 'QTUM',
            'BTG' => 'BTG',
            'EOS' => 'EOS'
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


        // exchangeRate
        $exchangeRate = $this->getExchangeRate();


        // make api call
        $version = '';
        $url = $this->apiBase . $version . '/public/ticker/ALL';
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
        $data = $dataOriginal['data'];


        // Finalize data. [id, currency_id, price]
        $currencies = array_map(function($currency) use($data, $exchangeRate){
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
                    $level1 = doubleval($data[$currency['currency_id'][0]]['closing_price']) / $exchangeRate;
                    $level2 = doubleval($data[$currency['currency_id'][1]]['closing_price']) / $exchangeRate;
                    $currency['price'] = $level1 * $level2;
                }
                else { // 1 step: string
                    $currency['price'] = doubleval($data[$currency['currency_id']]['closing_price']) / $exchangeRate;
                }

            }
            else {
                $currency['price'] = null;
            }

            return $currency;

        }, $currencies);


        return $currencies;


    }


    private function getExchangeRate()
    {
        $url = 'http://finance.daum.net/exchange/hhmm/exchangeHhmm.daum?code=USD&page=1';
        $res = null;

        try{
            $res = Http::request(Http::GET, $url);
        }
        catch (\Exception $e){
            // Error handling
            echo($e->getMessage());
            exit();
        }

        $exchangeRate = null;
        $res->dom->filter('table.exchangeTB tbody tr')->each(function($node) use (&$exchangeRate){
            if($exchangeRate == null){
                $tds = $node->filter('td');
                if($tds->count() > 1){
                    $exchangeRate = $tds->eq(1)->text();
                }
            }
        });

        return doubleval($exchangeRate);
    }

}