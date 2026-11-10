<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('furnishing')->nullable();
            $table->longText('facilities')->nullable();
            $table->integer('parking_lot')->nullable();
            $table->longText('amenities')->nullable();
            $table->string('full_name', 100)->nullable();
            $table->string('phone')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('living_image')->nullable();
            $table->string('bedroom_image')->nullable();
            $table->string('kitchen_image')->nullable();
            $table->string('bathroom_image')->nullable();
            $table->string('floorplan_image')->nullable();
            $table->string('residential_type')->nullable();
            $table->string('property_type')->nullable();
            $table->longText('property_info')->nullable(); // property_description, property_title, land_size, unit_number, property_size, lot_number
            $table->string('tenant_preference')->nullable();
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
        Schema::dropIfExists('property_details');
    }
};
