<?php
namespace App\Tasks;

use App\Currency;
use App\CurrencyPriceRecord;
use App\CurrencyPriceRecordLine;
use App\Exchanges\Clients\Binance;
use App\Exchanges\Clients\Bithumb;
use App\Exchanges\Clients\Kraken;
use App\Exchanges\Traits\MathTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Price {

    use MathTrait;


    /***
     * Parse from exchanges and add price record of current time
     */
    public function addCurrencyPriceRecord()
    {
        // get time before any api calls
        $time = Carbon::now();

        // Currencies to get prices
        $currencies = Currency
            ::orderBy('order')
            ->get(['id', 'currency_code']);


        // GET PRICE FROM EXCHANGES

        //$kraken = new Kraken();
        //$krakenPrices = $kraken->getPrices($currencies);

        $binance = new Binance();
        $binancePrices = $binance->getPrices($currencies);

        $bithumb = new Bithumb();
        $bithumbPrices = $bithumb->getPrices($currencies);



        // Add Record

        $record = CurrencyPriceRecord::create([
            'recorded_at' => $time
        ]);

        foreach($currencies as $currency){
            $baseExchangeCode = $currency->base_exchange->exchange_code;
            $basePricesVariable = $baseExchangeCode . 'Prices';
            $basePrices = $$basePricesVariable;

            $premExchangeCode = $currency->prem_exchange->exchange_code;
            $premPricesVariable = $premExchangeCode . 'Prices';
            $premPrices = $$premPricesVariable;

            $basePrice = $basePrices[$currency->currency_code]['price'];
            $premPrice = $premPrices[$currency->currency_code]['price'];
            $premAmt = doubleval($premPrice) - doubleval($basePrice);
            $premRate = $premAmt / doubleval($basePrice);

            // Standard Deviation
            $prevRecords = CurrencyPriceRecordLine::where('currency_id', $currency->id)
                ->orderBy('created_at', 'desc')
                ->limit(479)
                ->get();

            $sd5 = $this->getStandardDeviations($prevRecords, 4, [$basePrice, $premPrice, $premRate]);
            $sd10 = $this->getStandardDeviations($prevRecords, 9, [$basePrice, $premPrice, $premRate]);
            $sd30 = $this->getStandardDeviations($prevRecords, 29, [$basePrice, $premPrice, $premRate]);
            $sd60 = $this->getStandardDeviations($prevRecords, 59, [$basePrice, $premPrice, $premRate]);
            $sd120 = $this->getStandardDeviations($prevRecords, 119, [$basePrice, $premPrice, $premRate]);
            $sd240 = $this->getStandardDeviations($prevRecords, 239, [$basePrice, $premPrice, $premRate]);


            // add data
            $data = [
                'currency_id' => $currency->id,

                'base_exchange_id' => $currency->base_exchange->id,
                'prem_exchange_id' => $currency->prem_exchange->id,
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


    public function clearPriceRecords()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CurrencyPriceRecord::truncate();
        CurrencyPriceRecordLine::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }









    protected function getStandardDeviations($records, $limit, $start)
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
            'bp' => $this->standardDeviation($bps) / $bps[0],
            'pp' => $this->standardDeviation($pps) / $pps[0],
            'pr' => $this->standardDeviation($prs) / $prs[0],
        ];
    }


}