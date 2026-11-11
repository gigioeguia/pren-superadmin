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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('puid')->unique();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('house_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('building_name', 100)->nullable();
            $table->double('utility_deposit')->default(0)->nullable();
            $table->double('security_deposit')->default(0)->nullable();
            $table->text('cover_image')->nullable();
            $table->longText('room_info')->nullable(); // bedroom,bathroom,room_size,unit_size,floor_rang
            $table->integer('status')->default(0); // pending = 0 || approve = 1 || inactive = 2 || rejected = 3
            $table->longText('rent_info')->nullable(); // property_rent,monthly_rent,min_rental_unit,min_rental_value,completion_year,security_deposit_unit,security_deposit_value
            $table->longText('address_info')->nullable(); // country, city, state, address, postcode
            $table->longText('meta')->nullable();
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
        Schema::dropIfExists('properties');
    }
};
