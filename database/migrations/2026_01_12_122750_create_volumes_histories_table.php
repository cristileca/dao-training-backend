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
        Schema::create('volumes_histories', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('user_id')->index();
            $table->foreignUuid('from_user_id')->index()->nullable();
            $table->foreignUuid('volume_id')->index();
            $table->double('price', 8, 2)->index();
            $table->enum('type', ['personal', 'infinity'])->index();
            $table->double("old")->index()->default(0);
            $table->double("new")->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volumes_histories');
    }
};
