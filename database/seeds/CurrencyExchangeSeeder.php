<?php

use App\Currency;
use App\Exchange;
use Illuminate\Database\Seeder;

class CurrencyExchangeSeeder extends Seeder
{
    public function exchanges()
    {
        return [
            [
                'exchange_name' => 'Bithumb',
                'exchange_code' => 'bithumb',
                'exchange_url' => 'https://www.bithumb.com',
                'exchange_base' => 'KR'
            ],
            [
                'exchange_name' => 'Kraken',
                'exchange_code' => 'kraken',
                'exchange_url' => 'https://www.kraken.com',
                'exchange_base' => 'US'
            ],
            [
                'exchange_name' => 'Binance',
                'exchange_code' => 'binance',
                'exchange_url' => 'https://www.binance.com',
                'exchange_base' => 'HK'
            ]
        ];
    }

    public function currencies()
    {
        return [
            [
                'currency_code' => 'BTC',
                'currency_name' => 'Bitcoin',
                'order' => 1,
                'is_active' => false,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'BCH',
                'currency_name' => 'Bitcoin Cash',
                'order' => 2,
                'is_active' => false,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'ETH',
                'currency_name' => 'Ethereum',
                'order' => 3,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'LTC',
                'currency_name' => 'Litecoin',
                'order' => 4,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'XRP',
                'currency_name' => 'Ripple',
                'order' => 5,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'DASH',
                'currency_name' => 'Dash',
                'order' => 6,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'ETC',
                'currency_name' => 'Ethereum Classic',
                'order' => 7,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],

            [
                'currency_code' => 'XMR',
                'currency_name' => 'Monero',
                'order' => 8,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'ZEC',
                'currency_name' => 'Zcash',
                'order' => 9,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'QTUM',
                'currency_name' => 'Qtum',
                'order' => 10,
                'is_active' => true,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'BTG',
                'currency_name' => 'Bitcoin Gold',
                'order' => 11,
                'is_active' => false,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ],
            [
                'currency_code' => 'EOS',
                'currency_name' => 'Eos',
                'order' => 12,
                'is_active' => false,
                'base_exchange' => 'Binance',
                'prem_exchange' => 'Bithumb'
            ]
        ];
    }


    public function run()
    {
        foreach($this->exchanges() as $data){
            Exchange::create($data);
        }

        foreach($this->currencies() as $data){
            $currency = Currency::create([
                'currency_code' => $data['currency_code'],
                'currency_name' => $data['currency_name'],
                'order' => $data['order'],
                'is_active' => $data['is_active']
            ]);

            $baseExchange = Exchange::findByName($data['base_exchange']);
            $premExchange = Exchange::findByName($data['prem_exchange']);
            $currency->exchanges()->create([
                'base_exchange_id' => $baseExchange->id,
                'prem_exchange_id' => $premExchange->id
            ]);
        }
    }
}
