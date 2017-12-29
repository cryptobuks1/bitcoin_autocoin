<?php

namespace App\Console\Commands;

use App\CurrencyPriceRecord;
use Illuminate\Console\Command;

class CurrencyPriceClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autocoin:price:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear currency price database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CurrencyPriceRecord::truncate();
    }
}
