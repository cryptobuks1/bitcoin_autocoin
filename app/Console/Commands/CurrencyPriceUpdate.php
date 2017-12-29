<?php

namespace App\Console\Commands;

use App\CurrencyPriceRecord;
use Illuminate\Console\Command;

class CurrencyPriceUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autocoin:price:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get prices and update the database';

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
        CurrencyPriceRecord::addRecord();
    }
}
