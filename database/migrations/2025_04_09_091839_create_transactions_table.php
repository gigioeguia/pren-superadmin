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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained("users")->nullOnDelete();
            $table->foreignId('gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('security_deposit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rent_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdraw_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_subscribe_id')->nullable()->constrained()->nullOnDelete();
            $table->datetime('date');
            $table->double('amount');
            $table->double('charge')->default(0);
            $table->string('type')->nullable(); // debit || credit ( subject to Admin)
            $table->string('payment_method')->nullable();
            $table->string('payment_type')->nullable(); // security_deposit, rent_payment, subscription, withdraw
            $table->string('file')->nullable();
            $table->text('notes')->nullable();
            $table->text('meta')->nullable(); // cheque or others infos.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
