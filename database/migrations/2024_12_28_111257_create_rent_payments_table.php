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
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable();
            $table->foreignId('rent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->double('monthly_rent', 10, 2)->default(0);
            $table->double('utility_bill', 10, 2)->default(0);
            $table->double('charge', 10, 2)->default(0);
            $table->double('subtotal_amount', 10, 2)->default(0);
            $table->double('total_amount', 10, 2)->default(0);
            $table->string('rent_month')->nullable();
            $table->string('rent_year')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, rejected, pending
            $table->string('payment_data')->nullable(); // json format
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
        Schema::dropIfExists('rent_payments');
    }
};
