<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('punch_in_time')->nullable();
            $table->string('punch_in_latitude')->nullable();
            $table->string('punch_in_longitude')->nullable();
            $table->string('punch_in_location')->nullable();
            $table->string('punch_out_time')->nullable();
            $table->string('punch_out_latitude')->nullable();
            $table->string('punch_out_longitude')->nullable();
            $table->string('punch_out_location')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
