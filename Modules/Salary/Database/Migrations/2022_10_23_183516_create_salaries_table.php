<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('month')->nullable();
            $table->string('date')->nullable();
            $table->string('salary')->nullable();
            $table->string('monthly_target_achieve')->default(0);
            $table->string('product_target_achieve')->default(0);
            $table->string('deduction')->default(0);
            $table->string('claim')->nullable();
            $table->morphs('salarytable');
            $table->enum('status',['1','2'])->default(2)->comment = "1 = Active , 2 = InActive";
            $table->string('type')->nullable();
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
        Schema::dropIfExists('salaries');
    }
}
