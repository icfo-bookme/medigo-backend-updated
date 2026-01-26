<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabourSalaryPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labour_salary_payment', function (Blueprint $table) {
            $table->id();
//            $table->unsignedBigInteger('labour_id');
//            $table->foreign('labour_id')->references('id')->on('labours');
            $table->bigInteger('labour_id');
            $table->bigInteger('created_by');
            $table->double('basic_salary')->default(0);
            $table->double('total_working_days')->default(0);
            $table->double('avg_overtime_hour')->default(0);
            $table->double('overtime_amount')->default(0);
            $table->double('total_allowance')->default(0);
            $table->double('total_gross_salary')->default(0);
            $table->double('avg_absence_hour')->default(0);
            $table->double('absence_amount')->default(0);
            $table->double('salary_advance')->default(0);
            $table->double('other_deduction')->default(0);
            $table->double('total_deduction')->default(0);
            $table->double('payable_salary')->default(0);
            $table->double('net_payable')->default(0);
            $table->double('bonus')->default(0);
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
        Schema::dropIfExists('labour_salary_payment');
    }
}
