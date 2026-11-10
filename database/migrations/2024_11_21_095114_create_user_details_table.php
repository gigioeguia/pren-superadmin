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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('mykad')->nullable();
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('mykad_id')->nullable();
            $table->string('lang')->default('en');
            $table->longText('company_info')->nullable();
            $table->longText('address_info')->nullable();
            $table->longText('nominee_info')->nullable();
            $table->longText('emergency_contact')->nullable();
            $table->longText('workplace')->nullable();
            $table->longText('vehicles_info')->nullable();
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
        Schema::dropIfExists('user_details');
    }
};
