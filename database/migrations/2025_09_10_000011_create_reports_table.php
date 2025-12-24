<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerformanceReportsTable  extends Migration
{
    public function up()
    {
        Schema::create('performance_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->decimal('sales', 15, 2);
            $table->string('cost_of_sell')->nullable();
            $table->string('metrial_cost');
            $table->string('salaries');
            $table->string('tour_travel')->nullable();
            $table->string('other_cost')->nullable();
            $table->string('unpaid_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
