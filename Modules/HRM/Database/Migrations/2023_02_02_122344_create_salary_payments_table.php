<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->integer('created_by');
            $table->double('basic_salary')->default(0);
            $table->double('gross_salary')->default(0);
            $table->double('salary_advance')->default(0);
            $table->double('total_deduction')->default(0);
            $table->double('net_salary')->default(0);
            $table->double('provident_fund')->default(0);
            $table->double('bonus')->default(0);
            $table->double('payment_amount')->default(0);
            $table->string('payment_month');
            $table->tinyInteger('payment_type')->comment('1 for cash payment, 2 for chaque payment & 3 for bank payment');
            $table->text('note')->nullable();
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
        Schema::dropIfExists('salary_payments');
    }
}
