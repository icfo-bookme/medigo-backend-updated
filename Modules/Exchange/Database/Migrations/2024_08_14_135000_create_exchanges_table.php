<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('return_no')->unique();
            $table->string('invoice_no');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->double('total_price');
            $table->double('prv_pay_amount')->nullable();
            $table->double('paid_amount')->default(0);
            $table->double('grand_total');
            $table->text('reason')->nullable();
            $table->date('sale_date');
            $table->date('exchange_date');
            $table->double('exchange_qty')->nullable();
            $table->double('total_received_qty')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('return_rcv_status')->default(2)->comment('1=Return Received, 2=Return Not Received');
            $table->tinyInteger('payment_status')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchanges');
    }
}
