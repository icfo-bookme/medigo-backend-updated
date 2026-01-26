<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreignId('campaign_id ')->constrained('campaigns');
            $table->foreign('product_id')->references('id')->on('products');
            $table->bigInteger('product_unit_id');
            $table->string('name');
            $table->double('price');
            $table->double('campaign_price', 8, 2)->default(0.00);
            $table->double('discount')->nullable();
            $table->double('qty')->nullable();
            $table->double('alert_qty')->nullable();
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
        Schema::dropIfExists('product_units');
    }
}
