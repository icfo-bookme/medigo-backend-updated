<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('customer_id');
            $table->integer('limit_count')->default(0);
            $table->integer('used_count')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1=Active, 2=Inactive');

            $table->foreign('coupon_id')->references('id')->on('coupons');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_coupons');
    }
}
