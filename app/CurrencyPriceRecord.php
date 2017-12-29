<?php

namespace App;

use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CurrencyPriceRecord extends Model
{
    protected $fillable = [
        'recorded_at',
        'base_exchange_id',
        'prem_exchange_id',
        'exchange_rate',
    ];

    protected $dates = [
        'recorded_at'
    ];


    /***
     * Add Record
     */
    public static function addRecord()
    {
        $currentTime = Carbon::now();



        // Get exchange rate

        $exchangeRate = doubleval(self::getExchangeRate());



        // Get active currencies

        $currencies = Currency
            ::active()
            ->orderBy('order')
            ->get(['id', 'currency_code']);



        // Get price from base exchange

        $basePrices = self::getPricesFromKraken($currencies);



        // Get price from prem exchange

        $premPrices = self::getPricesFromBithumb($currencies, $exchangeRate);




        // Add records

        $baseExchange = Exchange::findByName('Kraken');
        $premExchange = Exchange::findByName('Bithumb');



        $record = CurrencyPriceRecord::create([
            'recorded_at' => $currentTime,
            'base_exchange_id' => $baseExchange->id,
            'prem_exchange_id' => $premExchange->id,
            'exchange_rate' => $exchangeRate
        ]);


        foreach($currencies as $currency){
            $basePrice = $basePrices[$currency->currency_code]['price'];
            $premPrice = $premPrices[$currency->currency_code]['price'];
            $premAmt = doubleVal($premPrice) - doubleVal($basePrice);
            $premRate = $premAmt / doubleVal($basePrice);

            $data = [
                'currency_id' => $currency->id,
                'base_currency_price' => $basePrice,
                'prem_currency_price' => $premPrice,
                'prem_amount' => $premAmt,
                'prem_rate' => $premRate,
            ];

            $record->currencyPriceRecordLines()->create($data);
        }




    }


    protected static function getExchangeRate()
    {
        $apiURL = 'http://finance.daum.net/exchange/hhmm/exchangeHhmm.daum?code=USD&page=1';

        $res = self::request('GET', $apiURL);

        $exchangeRate = null;

        $res->dom->filter('table.exchangeTB tbody tr')->each(function($node) use (&$exchangeRate){
            if($exchangeRate == null){
                $tds = $node->filter('td');
                if($tds->count() > 1){
                    $exchangeRate = $tds->eq(1)->text();
                }
            }
        });

        return $exchangeRate;
    }


    protected static function getPricesFromKraken(Collection $currencies)
    {
        $currencyIDs = [
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

        $currencies = $currencies
            ->pluck('id', 'currency_code')
            ->map(function($id){
                return [
                    'id' => $id
                ];
            })->toArray();

        foreach($currencyIDs as $currencyCode => $currencyID){
            $currencies[$currencyCode]['currency_id'] = $currencyID;
        }

        $ids = [];

        foreach($currencies as $key => $currency){
            $ids[] = $currency['currency_id'];
        }

        $ids = implode(',', $ids);


        $url = 'https://api.kraken.com/0/public/Ticker?pair='.$ids;
        $res = self::request('GET', $url);

        $data = json_decode($res->content, true);

        $currencies = array_map(function($currency) use($data){
            $currency['price'] = $data['result'][$currency['currency_id']]['c'][0];
            return $currency;

        }, $currencies);


        return $currencies;
    }


    protected static function getPricesFromBithumb($currencies, $exchangeRate)
    {
        $currencyIDs = [
            'BTC' => 'BTC',
            'BCH' => 'BCH',
            'ETH' => 'ETH',
            'LTC' => 'LTC',
            'XRP' => 'XRP',
            'DASH' => 'DASH',
            'ETC' => 'ETC',
            'XMR' => 'XMR',
            'ZEC' => 'ZEC',
        ];

        $currencies = $currencies
            ->pluck('id', 'currency_code')
            ->map(function($id){
                return ['id' => $id];
            })->toArray();


        foreach($currencyIDs as $currencyCode => $currencyID){
            $currencies[$currencyCode]['currency_id'] = $currencyID;
        }


        $url = 'https://api.bithumb.com/public/ticker/ALL';
        $res = self::request('GET', $url);

        $data = json_decode($res->content, true);

        $currencies = array_map(function($currency) use($data, $exchangeRate){
            $currency['price'] = $data['data'][$currency['currency_id']]['closing_price'] / $exchangeRate;
            return $currency;

        }, $currencies);


        return $currencies;
    }







    protected static function request($method, $uri, $params=[], $headers=[])
    {
        $client = new Client();

        foreach($headers as $name => $value){
            $client->setHeader($name, $value);
        }

        $retries = 0;
        $retriesMax = config('autocoin.http_retries');
        $res = null;
        $responseCode = null;

        while($retries < $retriesMax){
            $res = $client->request($method, $uri, $params);
            $responseCode = $client->getResponse()->getStatus();

            if($responseCode == 200){
                break;
            }
            else {
                usleep(config('autocoin.http_retries_interval'));
                $retries++;
            }
        }

        if($responseCode != 200){
            throw new \Exception(sprintf('Error while requesting %s. Status Code: %d', $uri, $responseCode));
        }

        $return = new \StdClass();
        $return->content = $client->getResponse()->getContent();
        $return->dom = $res;
        $return->status = $responseCode;

        return $return;
    }









    // Relations
    public function baseExchange()
    {
        return $this->belongsTo(Exchange::class);
    }

    public function premExchange()
    {
        return $this->belongsTo(Exchange::class);
    }

    public function currencyPriceRecordLines()
    {
        return $this->hasMany(CurrencyPriceRecordLine::class);
    }
}
