<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->unsignedBigInteger('transfer_warehouse_id');
            $table->foreign('transfer_warehouse_id')->references('id')->on('warehouses');
            $table->unsignedBigInteger('receive_warehouse_id');
            $table->foreign('receive_warehouse_id')->references('id')->on('warehouses');
            $table->bigInteger('total_qty')->nullable();
            $table->enum('status',['1','2'])->default('2')->comment = ' 1 = Active , 2 = InActive';
            $table->unsignedBigInteger('created_id');
            $table->foreign('created_id')->references('id')->on('users');
            $table->unsignedBigInteger('approved_id')->nullable();
            $table->foreign('approved_id')->references('id')->on('users');
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
        Schema::dropIfExists('stock_transfers');
    }
}
