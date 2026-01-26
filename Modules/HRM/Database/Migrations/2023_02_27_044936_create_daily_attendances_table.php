<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->bigInteger('shift_id');
            $table->date('check_in_date');
            $table->time('check_in_time');
            $table->date('check_out_date')->nullable();
            $table->time('check_out_time')->nullable();
            $table->double('working_hour')->nullable();
            $table->enum('dept_type', ['1', '2'])->comment('1 = Employee, 2 = Labour');
            $table->enum('approval_status', ['1', '2'])->default('1')->comment(' 1 = Pending, 2 = Approved');
            $table->enum('type', ['1', '2'])->comment('1 = On-Time, 2 = Absent');
            $table->string('approve_remarks')->nullable();
            $table->integer('approved_by')->nullable();
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
        Schema::dropIfExists('daily_attendances');
    }
}
