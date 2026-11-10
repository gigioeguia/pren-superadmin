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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('subscriptionName')->nullable();
            $table->integer('duration')->default(0);
            $table->string('duration_type', 50)->nullable(); // day, month, year
            $table->double('offerPrice', 10, 2)->default(0);
            $table->double('subscriptionPrice', 10, 2)->nullable();
            $table->integer('number_of_property')->default(-1); // -1 for unlimited
            $table->integer('number_of_tenant')->default(-1);
            $table->integer('number_of_labor')->default(-1);
            $table->longText('features')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('plans');
    }
};
