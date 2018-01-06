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

            // Standard Deviations (5min, 10min, 30min, 60min, 120min 240min)
            // base price
            $table->double('sd_bp_5', 5, 4)->nullable();
            $table->double('sd_bp_10', 5, 4)->nullable();
            $table->double('sd_bp_30', 5, 4)->nullable();
            $table->double('sd_bp_60', 5, 4)->nullable();
            $table->double('sd_bp_120', 5, 4)->nullable();
            $table->double('sd_bp_240', 5, 4)->nullable();

            // premium price
            $table->double('sd_pp_5', 5, 4)->nullable();
            $table->double('sd_pp_10', 5, 4)->nullable();
            $table->double('sd_pp_30', 5, 4)->nullable();
            $table->double('sd_pp_60', 5, 4)->nullable();
            $table->double('sd_pp_120', 5, 4)->nullable();
            $table->double('sd_pp_240', 5, 4)->nullable();

            // primium rate
            $table->double('sd_pr_5', 5, 4)->nullable();
            $table->double('sd_pr_10', 5, 4)->nullable();
            $table->double('sd_pr_30', 5, 4)->nullable();
            $table->double('sd_pr_60', 5, 4)->nullable();
            $table->double('sd_pr_120', 5, 4)->nullable();
            $table->double('sd_pr_240', 5, 4)->nullable();

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
