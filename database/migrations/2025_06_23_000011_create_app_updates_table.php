<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppUpdatesTable extends Migration
{
    public function up()
    {
        Schema::create('app_updates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('version');
            $table->string('heading');
            $table->longText('content');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
