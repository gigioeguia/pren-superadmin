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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_subscribe_id')->nullable();
            $table->string('unique_id')->unique();
            $table->string('name')->nullable();
            $table->string('role');
            $table->string('email')->unique();
            $table->string('image')->nullable();
            $table->longText('phone')->nullable(); // code & phone should be in json format.
            $table->string('password')->nullable();
            $table->boolean('is_setupped')->default(0);
            $table->double('balance')->default(0);
            $table->string('profile_type')->nullable();
            $table->timestamp('subscriptionDate')->nullable();
            $table->date('will_expire')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
