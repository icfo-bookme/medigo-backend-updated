<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangeProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_products', function (Blueprint $table) {
            $table->id();
            $table->integer('exchange_id');
            $table->string('invoice_no');
            $table->integer('old_product_id');
            $table->string('old_product_code');
            $table->double('old_stock_qty');
            $table->double('received_qty')->nullable();
            $table->double('old_price');
            $table->integer('product_id');
            $table->string('product_code');
            $table->double('stock_qty');
            $table->double('price');
            $table->double('old_exchange_qty');
            $table->double('charge_amount');
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
        Schema::dropIfExists('exchange_products');
    }
}
