<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');


            $table->string('serial_no')->nullable();
            $table->double('qty');
            $table->double('return_qty')->nullable();

            $table->unsignedBigInteger('sale_unit_id')->nullable();
            $table->foreign('sale_unit_id')->references('id')->on('units');
            $table->double('net_unit_price');


            $table->double('discount')->nullable();
            $table->double('discount_rate')->nullable();


            $table->double('tax_rate');
            $table->double('tax');
            $table->double('total');

            $table->tinyInteger('return_status')->default(0)->comment('0=Not Return, 8=Partial Return, 9=Full Return');
            $table->bigInteger('order_type')->nullable();

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
        Schema::dropIfExists('sale_products');
    }
}
