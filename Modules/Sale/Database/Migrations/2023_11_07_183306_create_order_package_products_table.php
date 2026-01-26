<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPackageProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_package_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_package_id')->constrained('order_packages')->onDelete('cascade');
            $table->unsignedBigInteger('sale_product_id')->nullable();
            $table->unsignedBigInteger('sale_unit_id')->nullable();

            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            $table->double('qty')->nullable();
            $table->double('price')->nullable();
            $table->double('net_unit_price')->nullable();
            $table->double('discount')->nullable();
            $table->double('discount_rate')->nullable();
            $table->double('total')->nullable();

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
        Schema::dropIfExists('order_package_products');
    }
}
