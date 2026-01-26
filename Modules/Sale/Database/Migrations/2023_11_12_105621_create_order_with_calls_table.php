<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderWithCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_with_calls', function (Blueprint $table) {
            $table->id();
            $table->string('facebook')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('mobile')->nullable();
            $table->enum('status',['active','inactive'])->default('active')->nullable();

            $table->string('modified_by')->nullable();
            $table->string('created_by')->nullable();

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
        Schema::dropIfExists('order_with_calls');
    }
}
