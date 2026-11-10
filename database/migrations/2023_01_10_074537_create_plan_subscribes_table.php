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
        Schema::create('plan_subscribes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // landlord id(auth id)
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->double('price')->default(0);
            $table->integer('number_of_property')->nullable();
            $table->integer('number_of_tenant')->nullable();
            $table->integer('number_of_labor')->nullable();
            $table->string('plan_name')->nullable();
            $table->string('payment_status')->nullable()->default('unpaid'); //paid, unpaid, rejected
            $table->integer('duration')->default(0); // in days
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('plan_subscribes');
    }
};
