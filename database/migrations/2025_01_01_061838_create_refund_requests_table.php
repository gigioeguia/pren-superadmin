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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('deposit_id')->constrained('security_deposits')->cascadeOnDelete();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reason_id')->nullable()->constrained('refund_reasons')->nullOnDelete();
            $table->double('amount', 10,2)->default(0.00);
            $table->string('status')->default('unpaid'); // unpaid || paid ||  rejected
            $table->text('refund_reason')->nullable();
            $table->string('attachment')->nullable();
            $table->longText('bank_info')->nullable(); // json format
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
        Schema::dropIfExists('refund_requests');
    }
};
