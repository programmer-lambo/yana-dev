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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("title");
            $table->string("slug")->unique();
            $table->mediumText("body");
            $table->boolean("is_indexed")->default(true);
            $table->foreignId('author_id')->constrained(
                table: 'users', indexName: 'notes_author_id'
            );

            $table->foreignId('category_id')->constrained(
                table: 'categories', indexName: 'notes_categories_id'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
