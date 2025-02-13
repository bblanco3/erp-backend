<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contactable_id');
                $table->string('contactable_type');
                $table->string('nickname')->nullable();
                $table->string('name');
                $table->string('role')->nullable();
                $table->string('position')->nullable();
                $table->string('department')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();

                $table->index(['contactable_id', 'contactable_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
