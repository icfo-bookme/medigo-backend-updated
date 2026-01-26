<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->double('grand_total')->nullable();
            $table->integer('auto_order_after_days')->nullable();
            $table->double('day_counter')->nullable();
            $table->double('shipping_cost')->nullable();
            $table->double('item')->nullable();
            $table->double('total_qty')->nullable();
            $table->double('total_discount')->nullable();
            $table->double('total_discount_amount')->nullable();
            $table->double('net_total')->nullable();

            $table->bigInteger('status')->nullable();

            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();

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
        Schema::dropIfExists('order_packages');
    }
}
