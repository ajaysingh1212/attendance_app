<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowReportsTable extends Migration
{
    public function up()
    {
        Schema::create('show_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
