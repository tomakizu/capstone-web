<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('road_network', function (Blueprint $table) {
            $table->increments('id');
            $table->string('network_name');
            $table->string('image_name');
            $table->integer('point_size');
        });

        Schema::create('sensor_position', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('road_network_id')->unsigned();
            $table->foreign('road_network_id')->references('id')->on('road_network');
            $table->integer('x_position');
            $table->integer('y_position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensor_position');
        Schema::dropIfExists('road_network');
    }
};
