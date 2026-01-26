<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_returns', function (Blueprint $table) {
            $table->id();
            $table->integer('warehouse_id');
            $table->string('return_no')->unique();
            $table->string('invoice_no');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->double('total_price');
            $table->double('total_deduction')->nullable();
            $table->double('tax_rate')->nullable();
            $table->double('total_tax')->nullable();
            $table->double('grand_total');
            $table->text('reason')->nullable();
            $table->date('date');
            $table->date('return_date');
            $table->enum('type', ['1', '2'])->comment("1=Sale,2=Purchase");
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('supplier_id')->references('id')->on('suppliers');

            $table->enum('payment_method', ['1', '2', '3'])->nullable()->comment = "1=Cash,2=Cheque,3=Mobile";
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->string('reference_no')->nullable();
            $table->enum('is_paid', ['1', '2'])->default('1')->comment("1=Not Paid,2=Paid");
            $table->enum('sale_payment_status', ['1', '2', '3'])->comment("1=Paid, 2=Partial, 3=Due");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_returns');
    }
}
