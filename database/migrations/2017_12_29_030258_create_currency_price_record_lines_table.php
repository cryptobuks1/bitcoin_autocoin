<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyPriceRecordLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_price_record_lines', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('currency_price_record_id')->unsigned();
            $table->foreign('currency_price_record_id')
                ->references('id')->on('currency_price_records')->onDelete('cascade');

            $table->integer('currency_id')->unsigned();
            $table->foreign('currency_id')
                ->references('id')->on('currencies')->onDelete('cascade');

            $table->double('base_currency_price', 20, 6);

            $table->double('prem_currency_price', 20, 6);

            // Other fields
            $table->double('prem_amount', 20, 6);
            $table->double('prem_rate', 5, 4);


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
        Schema::dropIfExists('currency_price_record_lines');
    }
}
