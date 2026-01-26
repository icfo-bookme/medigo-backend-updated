<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->float('item');
            $table->float('total_qty');
            $table->double('total_discount');
            $table->double('total_tax');
            $table->double('total_cost');
            $table->double('order_tax_rate')->nullable();
            $table->double('order_tax')->nullable();
            $table->double('order_discount')->nullable();
            $table->double('shipping_cost')->nullable();
            $table->double('grand_total');
            $table->double('paid_amount');
            $table->double('due_amount');
            $table->enum('purchase_status',['1','2','3','4'])->comment="1=Received,2=Partial,3=Pending,4=Ordered";
            $table->enum('payment_status',['1','2','3'])->comment="1=Paid,2=Partial,3=Due";
            $table->enum('payment_method',['1','2','3'])->comment="1=Cash,2=Cheque,3=Mobile";
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->date('purchase_date');
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
        Schema::dropIfExists('purchases');
    }
}
