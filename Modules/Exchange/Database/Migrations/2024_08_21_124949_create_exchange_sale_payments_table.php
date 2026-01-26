<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangeSalePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_sale_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exchange_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->double('payment_method')->nullable();
            $table->double('paid_amount')->nullable();
            $table->timestamps();

            $table->foreign('exchange_id')->references('id')->on('exchanges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_sale_payments');
    }
}
