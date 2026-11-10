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
        Schema::create('security_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->double('deposit_amount', 10, 2)->default(0);
            $table->double('rent_advance', 10, 2)->default(0);
            $table->double('utility_advance', 10, 2)->default(0);
            $table->double('total_amount', 10, 2)->default(0);
            $table->double('charge', 10, 2)->default(0);
            $table->double('landlord_balance', 10, 2)->default(0); // After the charge, the amount will be added to the landlord's balance.
            $table->timestamp('payment_date')->nullable();
            $table->string('status')->nullable(); // paid || unpaid || refund || rejected || pending
            $table->longText('payment_data')->nullable(); // json format
            $table->text('note')->nullable();
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
        Schema::dropIfExists('security_deposits');
    }
};

