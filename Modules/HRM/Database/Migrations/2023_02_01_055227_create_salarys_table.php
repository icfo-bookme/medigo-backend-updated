<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salarys', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->tinyInteger('employee_type')->comment('1= Provision , 2 = Permanent, 3 = Full Time , 4 = Part Time');
            $table->double('basic_salary')->default(0);
            $table->double('house_rent_allowance')->default(0);
            $table->double('medical_allowance')->default(0);
            $table->double('transport_allowance')->default(0);
            $table->double('mobile_allowance')->default(0);
            $table->double('other_allowance')->default(0);
            $table->double('provident_fund_contribution')->default(0);
            $table->double('tax_deduction')->default(0);
            $table->double('provident_fund_deduction')->default(0);
            $table->double('other_deduction')->default(0);
            $table->double('gross_salary')->default(0);
            $table->double('total_deduction')->default(0);
            $table->double('net_salary')->default(0);
            $table->date('date');
            $table->string('month');
            $table->tinyInteger('status')->default(1)->comment('1 for Active, 2 for Retired');
            $table->date('retired_date')->nullable();
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
        Schema::dropIfExists('salarys');
    }
}
