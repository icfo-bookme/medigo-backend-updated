<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->float('item');
            $table->float('total_qty');
            $table->float('total_return_qty')->nullable();

            $table->double('total_discount');
            $table->double('total_tax');
            $table->double('total_price');

            $table->double('order_tax_rate')->nullable();
            $table->double('order_tax')->nullable();
            $table->enum('order_discount_per',['1','2'])->nullable()->comment('1=TK,2=%');
            $table->double('order_discount')->nullable();


            $table->double('shipping_cost')->nullable();
            $table->double('grand_total');

            $table->enum('adjustment_per',['1','2'])->nullable()->comment('1=Addition,2=Subtraction');
            $table->double('adjustment')->nullable();

            $table->double('net_total');
            $table->double('paid_amount')->nullable();
            $table->double('change_amount')->nullable();

            $table->enum('payment_method',['1','2','3'])->nullable()->comment="1=Cash,2=Cheque,3=Mobile";
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->string('reference_no')->nullable();

            $table->date('sale_date');
            $table->enum('delivery_status',['1','2'])->default(1)->comment="1=Pending,2=Delivered";
            $table->tinyInteger('payment_status')->default(3)->comment="1=Paid,2=Partial,3=Due";
            $table->date('delivery_date')->nullable();
            $table->dateTime('est_delivery_date')->nullable();

            $table->integer('point')->nullable();
            $table->integer('use_point')->nullable();

            $table->bigInteger('sale_type')->nullable();
            $table->bigInteger('order_type')->nullable();
            $table->bigInteger('coupon_id')->nullable();
            $table->double('coupon_discount_value')->nullable();


            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('information')->nullable();
            $table->string('optional_information')->nullable();


            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
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
        Schema::dropIfExists('sales');
    }
}
