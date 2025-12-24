<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMakeCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('make_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_code');
            $table->string('shop_name');
            $table->string('owner_name');
            $table->string('phone_number');
            $table->string('email');
            $table->string('pincode')->nullable();
            $table->longText('address_line_1')->nullable();
            $table->longText('address_line_2')->nullable();
            $table->string('area')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('business_type')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('license_no')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('preferred_payment_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('account_no')->nullable();
            $table->longText('notes')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
