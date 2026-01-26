<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignLeavesTable extends Migration
{

    public function up()
    {
        Schema::create('assign_leaves', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->bigInteger('leave_category_id');
            $table->double('number_of_days');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assign_leaves');
    }
}
