<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllowancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Allowances', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by');
            $table->integer('employee_id');
            $table->string('allowances_name');
            $table->string('allowances_month');
            $table->string('allowances_amount');
            $table->text('allowances_description');
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
        Schema::dropIfExists('Allowances');
    }
}
