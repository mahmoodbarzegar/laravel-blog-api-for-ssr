<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('posts', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views')->default(0);

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->string('og_image')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'published_at']);

            // FULLTEXT indexes are MySQL-only — guarded so the same migration
            // also works against SQLite (used for fast automated tests).
            if ($driver === 'mysql') {
                $table->fullText(['title', 'excerpt', 'content']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
