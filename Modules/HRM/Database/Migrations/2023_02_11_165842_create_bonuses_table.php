<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by');
            $table->integer('employee_id')->nullable();
            $table->string('bonus_name');
            $table->string('bonus_month');
            $table->string('bonus_percentage');
            $table->text('bonus_description');
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
        Schema::dropIfExists('bonuses');
    }
}
