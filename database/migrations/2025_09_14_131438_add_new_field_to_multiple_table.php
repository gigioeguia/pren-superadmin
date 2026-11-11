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
        Schema::table('property_details', function (Blueprint $table) {

            $table->dropColumn(['living_image', 'bedroom_image', 'kitchen_image', 'bathroom_image']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->string('type')->nullable()->after('status');
            $table->double('sale_amount', 10, 2)->default(0)->after('security_deposit');
            $table->double('late_fee', 10, 2)->default(0)->after('sale_amount');
            $table->integer('late_fee_after_days')->default(0)->after('late_fee');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('property_id')->constrained('units')->nullOnDelete();
        });

        Schema::table('security_deposits', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('property_id')->constrained('units')->nullOnDelete();
        });

        Schema::table('rents', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('property_id')->constrained('units')->nullOnDelete();
        });

        Schema::table('gateways', function (Blueprint $table) {
            $table->double('min_amount')->default(0)->after('charge');
            $table->double('max_amount')->default(10000)->after('charge');
        });

        Schema::table('rent_payments', function (Blueprint $table) {
            $table->double('late_fee')->default(0)->after('total_amount');
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->foreignId('landlord_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->foreignId('landlord_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_details', function (Blueprint $table) {
            $table->string('living_image')->nullable();
            $table->string('bedroom_image')->nullable();
            $table->string('kitchen_image')->nullable();
            $table->string('bathroom_image')->nullable();
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('sale_amount');
            $table->dropColumn('late_fee');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });

        Schema::table('security_deposits', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });

        Schema::table('rents', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });

        Schema::table('gateways', function (Blueprint $table) {
            $table->dropColumn(['min_amount', 'max_amount']);
        });

        Schema::table('rent_payments', function (Blueprint $table) {
            $table->dropColumn('late_fee');
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn('landlord_id');
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropColumn('landlord_id');
        });
    }
};
