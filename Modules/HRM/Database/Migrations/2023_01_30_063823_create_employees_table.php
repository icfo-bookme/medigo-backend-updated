<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('name');
            $table->string('username');
            $table->string('email', 100)->unique()->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('gender');

            $table->string('present_address');
            $table->string('permanent_address')->nullable();
            $table->string('home_district')->nullable();

            $table->text('academic_qualification')->nullable();
            $table->text('professional_qualification')->nullable();
            $table->date('joining_date')->nullable();
            $table->text('experience')->nullable();
            $table->text('reference')->nullable();

            $table->tinyInteger('id_name')->nullable()->comment('1 for NID, 2 Passport, 3 for Driving License');
            $table->string('id_number')->nullable();
            $table->string('contact_no_one', 30);
            $table->string('contact_no_two', 30)->nullable();
            $table->string('emergency_contact', 30)->nullable();
            $table->string('web')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->tinyInteger('marital_status')->nullable()->comment('1 for Married, Single, 3 for Divorced, 4 for Separated, 5 for Widowed');
            $table->integer('branch_id')->nullable();
            $table->integer('designation_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->tinyInteger('activation_status')->default(1)->comment('1 for Active, 2 for Inactive');
            $table->string('blood_group')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 for Active, 2 for Retired');
            $table->date('retired_date')->nullable();
            $table->tinyInteger('deletion_status')->default(0);
            $table->date('date');
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->string('resume')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
