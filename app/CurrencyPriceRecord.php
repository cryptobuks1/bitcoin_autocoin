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
            //::active()
            ::orderBy('order')
            ->get(['id', 'currency_code']);



        // Get price from base exchange

        $basePrices = self::getPricesFromBinance($currencies);



        // Get price from prem exchange

        $premPrices = self::getPricesFromBithumb($currencies, $exchangeRate);




        // Add records

        $baseExchange = Exchange::findByName('Binance');
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
            $premAmt = doubleval($premPrice) - doubleval($basePrice);
            $premRate = $premAmt / doubleval($basePrice);


            // standard deviation
            $prevRecords = CurrencyPriceRecordLine::where('currency_id', $currency->id)
                ->orderBy('created_at', 'desc')
                ->limit(479)
                ->get();

            $sd5 = self::getStandardDeviations($prevRecords, 4, [$basePrice, $premPrice, $premRate]);
            $sd10 = self::getStandardDeviations($prevRecords, 9, [$basePrice, $premPrice, $premRate]);
            $sd30 = self::getStandardDeviations($prevRecords, 29, [$basePrice, $premPrice, $premRate]);
            $sd60 = self::getStandardDeviations($prevRecords, 59, [$basePrice, $premPrice, $premRate]);
            $sd120 = self::getStandardDeviations($prevRecords, 119, [$basePrice, $premPrice, $premRate]);
            $sd240 = self::getStandardDeviations($prevRecords, 239, [$basePrice, $premPrice, $premRate]);



            // add data
            $data = [
                'currency_id' => $currency->id,
                'base_currency_price' => $basePrice,
                'prem_currency_price' => $premPrice,
                'prem_amount' => $premAmt,
                'prem_rate' => $premRate,

                'sd_bp_5' => $sd5['bp'],
                'sd_bp_10' => $sd10['bp'],
                'sd_bp_30' => $sd30['bp'],
                'sd_bp_60' => $sd60['bp'],
                'sd_bp_120' => $sd120['bp'],
                'sd_bp_240' => $sd240['bp'],
                'sd_pp_5' => $sd5['pp'],
                'sd_pp_10' => $sd10['pp'],
                'sd_pp_30' => $sd30['pp'],
                'sd_pp_60' => $sd60['pp'],
                'sd_pp_120' => $sd120['pp'],
                'sd_pp_240' => $sd240['pp'],
                'sd_pr_5' => $sd5['pr'],
                'sd_pr_10' => $sd10['pr'],
                'sd_pr_30' => $sd30['pr'],
                'sd_pr_60' => $sd60['pr'],
                'sd_pr_120' => $sd120['pr'],
                'sd_pr_240' => $sd240['pr'],
            ];

            $record->currencyPriceRecordLines()->create($data);
        }




    }


    protected static function getStandardDeviations($records, $limit, $start)
    {
        $return = [
            'bp' => null,
            'pp' => null,
            'pr' => null,
        ];

        if(count($records) < $limit){
            return $return;
        }

        $bps = [doubleval($start[0])];
        $pps = [doubleval($start[1])];
        $prs = [doubleval($start[2])];

        for($i=0; $i<$limit; $i++){
            $bps[] = $records[$i]['base_currency_price'];
            $pps[] = $records[$i]['prem_currency_price'];
            $prs[] = $records[$i]['prem_rate'];
        }

        // Return the standard deviation as rate
        return [
            'bp' => stats_standard_deviation($bps) / $bps[0],
            'pp' => stats_standard_deviation($pps) / $pps[0],
            'pr' => stats_standard_deviation($prs) / $prs[0],
        ];
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
            'QTUM' => null,
            'BTG' => null,
            'EOS' => null
        ];

        $currencies = $currencies
            ->pluck('id', 'currency_code')
            ->map(function($id){
                return [
                    'id' => $id
                ];
            })->toArray();

        foreach($currencyIDs as $currencyCode => $currencyID){
            if($currencies[$currencyCode]) {
                $currencies[$currencyCode]['currency_id'] = $currencyID;
            }
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
            if(isset($data['result'][$currency['currency_id']])){
                $currency['price'] = $data['result'][$currency['currency_id']]['c'][0];
            }else{
                $currency['price'] = null;
            }

            return $currency;

        }, $currencies);


        return $currencies;
    }

    protected static function getPricesFromBinance($currencies)
    {
        $currencyIDs = [
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


        $url = 'https://api.binance.com/api/v3/ticker/price';
        $res = self::request('GET', $url);

        $data = [];
        $dataOriginal = json_decode($res->content, true);
        foreach($dataOriginal as $cur){
            $data[$cur['symbol']] = $cur;
        }


        //dd($currencies, $data);

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
                    $currency['price'] = $data[$currency['currency_id']]['price'];
                }

            }
            else {
                $currency['price'] = null;
            }

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
            'QTUM' => 'QTUM',
            'BTG' => 'BTG',
            'EOS' => 'EOS'
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






if (!function_exists('stats_standard_deviation')) {
    /**
     * This user-land implementation follows the implementation quite strictly;
     * it does not attempt to improve the code or algorithm in any way. It will
     * raise a warning if you have fewer than 2 values in your array, just like
     * the extension does (although as an E_USER_WARNING, not E_WARNING).
     *
     * @param array $a
     * @param bool $sample [optional] Defaults to false
     * @return float|bool The standard deviation or false on error.
     */
    function stats_standard_deviation(array $a, $sample = false) {
        $n = count($a);
        if ($n === 0) {
            trigger_error("The array has zero elements", E_USER_WARNING);
            return false;
        }
        if ($sample && $n === 1) {
            trigger_error("The array has only 1 element", E_USER_WARNING);
            return false;
        }
        $mean = array_sum($a) / $n;
        $carry = 0.0;
        foreach ($a as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        };
        if ($sample) {
            --$n;
        }
        return sqrt($carry / $n);


    }
}