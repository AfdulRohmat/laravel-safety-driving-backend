<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripMonitoringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_monitorings', function (Blueprint $table) {
            $table->id('trip_monitoring_id');
            $table->string('heart_rate');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('kecepatan');
            $table->string('rpm');
            $table->string('thurttle');
            $table->string('sudut_postural');
            $table->string('kecepatan_postural');
            $table->string('durasi_postural');
            $table->string('status');
            $table->string('trip_token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_monitorings');
    }
}
