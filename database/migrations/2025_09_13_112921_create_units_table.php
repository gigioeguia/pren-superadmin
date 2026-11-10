<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            // Basic details
            $table->string('unit_name');
            $table->string('floor_range')->nullable();
            $table->string('bedroom')->nullable();
            $table->string('bathroom')->nullable();
            $table->string('kitchen')->nullable();
            $table->string('balcony')->nullable();
            $table->string('condition')->nullable();
            $table->string('parking_lot')->nullable();
            $table->string('unit_size')->nullable();
            $table->longText('facilities')->nullable();
            $table->longText('amenities')->nullable();
            $table->string('tenant_preference')->nullable();
            $table->text('description')->nullable();
            $table->longText('images')->nullable();

            // Rent-related
            $table->double('monthly_rent', 10, 2)->default(0);
            $table->double('sale_amount', 10, 2)->default(0);
            $table->string('min_rental_value')->nullable();
            $table->string('min_rental_unit')->nullable();
            $table->double('security_deposit', 10, 2)->default(0);
            $table->string('security_deposit_value')->nullable();
            $table->string('security_deposit_unit')->nullable();
            $table->double('utility_deposit', 10, 2)->default(0);
            $table->double('late_fee', 10, 2)->default(0);
            $table->integer('late_fee_after_days')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
