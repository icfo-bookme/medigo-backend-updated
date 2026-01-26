<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('starting_time');
            $table->time('ending_time');
            $table->enum('status', ['1', '2'])->comment('1 = Active , 2 = Deleted');
            $table->enum('department', ['1', '2'])->comment('1 = Employee , 2 = Labor');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->string('deleted_by')->nullable();
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
        Schema::dropIfExists('shifts');
    }
}
