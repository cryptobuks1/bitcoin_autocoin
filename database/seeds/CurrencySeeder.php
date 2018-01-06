<?php

use App\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            [
                'currency_code' => 'BTC',
                'currency_name' => 'Bitcoin',
                'order' => 1,
                'is_active' => false
            ],
            [
                'currency_code' => 'BCH',
                'currency_name' => 'Bitcoin Cash',
                'order' => 2,
                'is_active' => false
            ],
            [
                'currency_code' => 'ETH',
                'currency_name' => 'Ethereum',
                'order' => 3,
            ],
            [
                'currency_code' => 'LTC',
                'currency_name' => 'Litecoin',
                'order' => 4,
            ],
            [
                'currency_code' => 'XRP',
                'currency_name' => 'Ripple',
                'order' => 5,
            ],
            [
                'currency_code' => 'DASH',
                'currency_name' => 'Dash',
                'order' => 6,
                'is_active' => false,
            ],
            [
                'currency_code' => 'ETC',
                'currency_name' => 'Ethereum Classic',
                'order' => 7,
            ],

            [
                'currency_code' => 'XMR',
                'currency_name' => 'Monero',
                'order' => 8,
            ],
            [
                'currency_code' => 'ZEC',
                'currency_name' => 'Zcash',
                'order' => 9,
            ],
            [
                'currency_code' => 'QTUM',
                'currency_name' => 'Qtum',
                'order' => 10,
            ],
            [
                'currency_code' => 'BTG',
                'currency_name' => 'Bitcoin Gold',
                'order' => 11,
                'is_active' => false
            ],
            [
                'currency_code' => 'EOS',
                'currency_name' => 'Eos',
                'order' => 12
            ]
        ];

        foreach($currencies as $currency){
            Currency::create($currency);
        }
    }
}
