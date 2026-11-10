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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('expense_for')->nullable();
            $table->string('payment_type')->default('Cash');
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('expense_date')->nullable();
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
        Schema::dropIfExists('expenses');
    }
};
