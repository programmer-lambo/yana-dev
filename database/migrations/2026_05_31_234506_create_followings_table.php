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
        Schema::create('followings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId("following_id")->constrained(
                table: "users", indexName: "following_user_id"
            );
            $table->foreignId("follower_id")->constrained(
                table: "users", indexName: "follower_user_id"
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followings');
    }
};
