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
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants');
                $table->foreignId('customer_id')->constrained('customers');
                $table->string('name');
                $table->text('details')->nullable();
                $table->date('due_date');
                $table->enum('status', ['active', 'completed', 'on-hold'])->default('active');
                $table->dateTime('created_on');
                $table->foreignId('created_by')->constrained('users');
                $table->dateTime('completed_on')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
