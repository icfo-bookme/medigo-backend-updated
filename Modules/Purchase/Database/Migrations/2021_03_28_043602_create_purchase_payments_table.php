<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->unsignedBigInteger('account_id');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->unsignedBigInteger('supplier_debit_transaction_id');
            $table->foreign('supplier_debit_transaction_id')->references('id')->on('transactions');
            $table->double('amount');
            $table->enum('payment_method',['1','2','3'])->comment="1=Cash,2=Cheque,3=Mobile";
            $table->string('reference_no')->nullable();
            $table->text('payment_note')->nullable();
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
        Schema::dropIfExists('purchase_payments');
    }
}
