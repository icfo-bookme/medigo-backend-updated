<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyClosingHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_closing_heads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_closing_id');
            $table->foreign('daily_closing_id')->references('id')->on('daily_closings');
            $table->unsignedBigInteger('closing_head_id');
            $table->foreign('closing_head_id')->references('id')->on('closing_heads');
            $table->double('amount');
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
        Schema::dropIfExists('daily_closing_heads');
    }
}
