<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_exchanges', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('currency_id')->unsigned();
            $table->foreign('currency_id')
                ->references('id')->on('currencies')->onDelete('cascade');

            $table->integer('base_exchange_id')->unsigned();
            $table->foreign('base_exchange_id')
                ->references('id')->on('exchanges')->onDelete('cascade');

            $table->integer('prem_exchange_id')->unsigned();
            $table->foreign('prem_exchange_id')
                ->references('id')->on('exchanges')->onDelete('cascade');


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
        Schema::dropIfExists('currency_exchanges');
    }
}
