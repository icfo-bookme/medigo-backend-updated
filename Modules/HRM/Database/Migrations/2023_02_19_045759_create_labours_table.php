<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaboursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labours', function (Blueprint $table) {
            $table->id();
            $table->string('labour_id')->nullable();
            $table->string('name');
            $table->string('gender');
            $table->string('present_address');
            $table->date('joining_date')->nullable();
            $table->date('joining_month')->nullable();
            $table->string('id_number')->nullable();
            $table->string('contact_no_one', 30);
            $table->string('emergency_contact', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->tinyInteger('marital_status')->nullable()->comment('1 for Married, Single, 3 for Divorced, 4 for Separated, 5 for Widowed');
            $table->integer('branch_id')->nullable();
            $table->string('blood_group')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 for Active, 2 for Retired');
            $table->date('retired_date')->nullable();
            $table->date('date');
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->string('resume')->nullable();
            $table->double('basic_salary')->nullable();
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
        Schema::dropIfExists('labours');
    }
}
