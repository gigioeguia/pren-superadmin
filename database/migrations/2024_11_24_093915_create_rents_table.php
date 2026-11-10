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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->double('monthly_rent', 10, 2)->nullable()->default(0);
            $table->double('utility_bill', 10, 2)->nullable()->default(0);
            $table->double('total_amount', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('landlord_agreement')->nullable();
            $table->string('tenant_agreement')->nullable();
            $table->string('status')->default('pending'); // pending / active / cancelled / expired / processing
            $table->longText('cancel_info')->nullable(); // title, prev_end_date(When created lease with end date), reason
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
        Schema::dropIfExists('rents');
    }
};
