<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();      // UUID ca primary key
            $table->string('name');
            $table->string('email')->unique();
            $table->json('user_tree')->nullable();
            $table->uuid('referral_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->double('volume')->default(0);
            $table->double('sales')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
