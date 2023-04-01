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
        Schema::create('actual_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('road_network_id')->unsigned();
            $table->foreign('road_network_id')->references('id')->on('road_network');
            $table->timestamp('timestamp');
            $table->integer('sensor_id')->unsigned();
            $table->foreign('sensor_id')->references('id')->on('sensor_position');
            $table->decimal('value', 10, 5);
        });
        Schema::create('predicted_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('road_network_id')->unsigned();
            $table->foreign('road_network_id')->references('id')->on('road_network');
            $table->timestamp('timestamp');
            $table->integer('sensor_id')->unsigned();
            $table->foreign('sensor_id')->references('id')->on('sensor_position');
            $table->decimal('value', 10, 5);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('predicted_data');
        Schema::dropIfExists('actual_data');
    }
};
