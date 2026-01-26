<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockReturnProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_return_products', function (Blueprint $table) {
            $table->id();
            $table->integer('warehouse_id');
            $table->unsignedBigInteger('stock_return_id');
            $table->string('invoice_no');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('item_code');
            $table->double('return_qty');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->double('product_rate');
            $table->double('deduction_rate')->nullable();
            $table->double('deduction_amount')->nullable();
            $table->double('total');
            $table->timestamps();

            $table->foreign('stock_return_id')->references('id')->on('stock_returns');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('product_variant_id')->references('id')->on('product_units');
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_return_products');
    }
}
