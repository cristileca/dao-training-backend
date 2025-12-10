<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_user_id')->index();
            $table->uuid('to_user_id')->index();
            $table->integer('level');
            $table->decimal('amount', 18, 8);
            $table->boolean('claimed')->default(false);
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
