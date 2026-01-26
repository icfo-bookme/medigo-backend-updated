<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryAllowancDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_allowanc_deductions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('salary_id')->unsigned();
            $table->foreign('salary_id')->references('id')->on('salarys');
            $table->bigInteger('head_id')->unsigned();
            $table->foreign('head_id')->references('id')->on('allowance_deductions');
            $table->double('head_value');
            $table->enum('type', ['1', '2'])->comment(' 1= alowance , 2 = deduction');
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
        Schema::dropIfExists('salary_allowanc_deductions');
    }
}
