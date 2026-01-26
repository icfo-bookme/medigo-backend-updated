<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeSalaryPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_salary_payments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('employee_id');
            $table->double('paid_amount');
            $table->bigInteger('account_id');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->enum('payment_method',['1','2','3'])->comment="1=Cash,2=Cheque,3=Mobile";
            $table->enum('status',['1','2'])->comment="1=Pending,2=Approved";
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
        Schema::dropIfExists('employee_salary_payments');
    }
}
