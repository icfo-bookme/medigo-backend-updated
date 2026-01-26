<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger("campaign_type")->default(1)->comment = "1=Product, 2=Category";
            $table->string("name");
            $table->string('slug')->unique();
            $table->enum('discount_type', ['percentage', 'amount'])->default('percentage');
            $table->float("discount_amount", 8, 2)->default(0);
            $table->string("image")->nullable();
            $table->dateTime("start_date")->format("Y-m-d");
            $table->dateTime("end_date")->format("Y-m-d");
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
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
        Schema::dropIfExists('campaigns');
    }
}
