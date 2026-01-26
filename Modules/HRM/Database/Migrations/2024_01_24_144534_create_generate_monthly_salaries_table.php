<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGenerateMonthlySalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generate_monthly_salaries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->bigInteger('shift_id');
            $table->integer('month');
            $table->integer('year');
            $table->integer('total_working_days');
            $table->integer('total_holidays');
            $table->integer('total_attended');
            $table->integer('total_paid_leaves');
            $table->integer('total_unpaid_leaves');
            $table->double('total_working_hours');
            $table->double('total_attended_hours');
            $table->double('increment_salary');
            $table->double('current_salary');
            $table->double('gross_salary');
            $table->double('net_salary');

            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->enum('status', ['1','2', '3'])->default('1')->comment('1 = Pending, 2 = Approved, 3 = Deleted');
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
        Schema::dropIfExists('generate_monthly_salaries');
    }
}
