<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOvertimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->double('working_hour');
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
        Schema::dropIfExists('overtimes');
    }
}
