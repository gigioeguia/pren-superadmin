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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained("users")->cascadeOnDelete(); // Tenant Id
            $table->foreignId('labor_id')->nullable()->constrained()->nullOnDelete(); // Assigned Labor
            $table->double('total_costing')->default(0); // Assigned Labor
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending | processing | completed | rejected
            $table->longText('images')->nullable();
            $table->text('notes')->nullable();
            $table->longText('comments')->nullable(); // process_comment || complete_comment, should be json format
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
        Schema::dropIfExists('maintenances');
    }
};
