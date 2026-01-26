<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->string('draft_no')->unique();
            $table->double('amount');
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->enum('status', ['1','2','3'])->comment('1 = pending, 2 = approved, 3 = canceled')->default('1');
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
        Schema::dropIfExists('drafts');
    }
}
