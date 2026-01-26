<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by');
            $table->integer('employee_id');
            $table->string('deduction_name');
            $table->string('deduction_month');
            $table->string('deduction_amount');
            $table->text('deduction_description');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('type')->comment('1 = Employee, 2 = Labour');
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
        Schema::dropIfExists('deductions');
    }
}
