<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('account_id');
                $table->boolean('is_online')->default(false);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();

                $table->foreign('account_id')->references('id')->on('accounts');
                $table->foreign('tenant_id')->references('id')->on('tenants');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
