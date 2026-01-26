<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('invoice_no', 255)->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->enum('type', ['1', '2'])->comment('1=Delivery, 2=Communication');
            $table->text('feedback');
            $table->timestamps();

            // Foreign Key
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('invoice_no')->references('invoice_no')->on('sales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_feedback');
    }
}
