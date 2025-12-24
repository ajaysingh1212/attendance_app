<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('location')->nullable();
            $table->string('visited_time')->nullable();
            $table->string('visited_out_latitude')->nullable();
            $table->string('visited_out_longitude')->nullable();
            $table->string('visited_out_location')->nullable();
            $table->string('visited_out_time')->nullable();
            $table->string('visited_duration')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
