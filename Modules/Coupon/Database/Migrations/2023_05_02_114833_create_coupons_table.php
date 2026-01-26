<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('coupon_type')->default(1)->comment('1 = General Coupon, 2 = Category Coupon, 3 = Customer Coupon');
            $table->tinyInteger('type')->default(1)->comment('1 = Fixed, 2 = Percentage');
            $table->double('value');
            $table->double('coupon_value_limit');
            $table->dateTime("start_date")->format("Y-m-d H:i:s");
            $table->dateTime("end_date")->format("Y-m-d H:i:s");
            $table->tinyInteger('status')->comment('1 = Active , 2  = Inactive');
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
        Schema::dropIfExists('coupons');
    }
}
