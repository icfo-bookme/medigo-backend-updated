<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangeProductReceivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_product_receives', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable();
            $table->bigInteger('exchange_id');
            $table->bigInteger('product_id');
            $table->string('product_code');
            $table->double('price')->nullable();
            $table->double('receive_qty')->nullable();
            $table->double('sub_total')->nullable();
            $table->date('receive_date')->nullable();
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
        Schema::dropIfExists('exchange_product_receives');
    }
}
