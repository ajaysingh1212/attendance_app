<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToMakeCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('make_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_category_id')->nullable();
            $table->foreign('shop_category_id', 'shop_category_fk_10689960')->references('id')->on('product_categories');
        });
    }
}
