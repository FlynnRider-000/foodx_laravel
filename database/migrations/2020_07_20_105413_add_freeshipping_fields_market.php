<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreeshippingFieldsMarket extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('markets', function (Blueprint $table) {
            $table->integer('shipping_method')->unsigned()->after('default_tax');
            $table->integer('free_shipping')->unsigned()->after('default_tax');
            $table->integer('limited_shipping')->unsigned()->after('default_tax');
            $table->integer('pay_on_pickup')->unsigned()->after('default_tax');
            $table->string('market_close_time')->unsigned()->after('information');
            $table->string('market_open_time')->unsigned()->after('information');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
