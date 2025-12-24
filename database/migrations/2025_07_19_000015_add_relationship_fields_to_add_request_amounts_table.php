<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToAddRequestAmountsTable extends Migration
{
    public function up()
    {
        Schema::table('add_request_amounts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_10658083')->references('id')->on('users');
        });
    }
}
