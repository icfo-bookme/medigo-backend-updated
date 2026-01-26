<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitorStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitor_stats', function (Blueprint $table) {
            $table->id();

            $table->string('ip_address')->nullable();

            $table->string('user_agent')->nullable();

            $table->timestamp('visited_at')->nullable();
            $table->string('visited_page')->nullable();

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
        Schema::dropIfExists('visitor_stats');
    }
}
