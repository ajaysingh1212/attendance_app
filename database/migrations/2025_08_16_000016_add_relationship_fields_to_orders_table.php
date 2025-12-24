<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('select_customer_id')->nullable();
            $table->foreign('select_customer_id', 'select_customer_fk_10689977')->references('id')->on('make_customers');
        });
    }
}
