<?php

use App\Exchange;
use Illuminate\Database\Seeder;

class ExchangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exchanges = [
            [
                'exchange_name' => 'Bithumb',
                'exchange_url' => 'https://www.bithumb.com',
                'exchange_base' => 'KR'
            ],
            [
                'exchange_name' => 'Kraken',
                'exchange_url' => 'https://www.kraken.com',
                'exchange_base' => 'US'
            ],
            [
                'exchange_name' => 'Binance',
                'exchange_url' => 'https://www.binance.com',
                'exchange_base' => 'HK'
            ]
        ];

        foreach($exchanges as $exchange){
            Exchange::create($exchange);
        }


    }
}
