<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackMembersTable extends Migration
{
    public function up()
    {
        Schema::create('track_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('latitude')->unique();
            $table->string('longitude')->unique();
            $table->string('location');
            $table->string('time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
