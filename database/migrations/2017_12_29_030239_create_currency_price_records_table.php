<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyPriceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_price_records', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('recorded_at');

            $table->integer('base_exchange_id')->unsigned();
            $table->foreign('base_exchange_id')
                ->references('id')->on('exchanges')->onDelete('cascade');

            $table->integer('prem_exchange_id')->unsigned();
            $table->foreign('prem_exchange_id')
                ->references('id')->on('exchanges')->onDelete('cascade');

            $table->double('exchange_rate', 20, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_price_records');
    }
}
