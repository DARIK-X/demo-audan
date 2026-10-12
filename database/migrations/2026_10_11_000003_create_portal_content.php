<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('articles', function (Blueprint $table) {
            $table->id(); $table->string('title', 180); $table->string('category', 30)->index();
            $table->string('excerpt', 400)->nullable(); $table->text('body');
            $table->string('image_path')->nullable(); $table->string('attachment_path')->nullable();
            $table->string('attachment_name', 180)->nullable(); $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable(); $table->timestamps();
            $table->index(['category', 'is_published', 'published_at']);
        });
        Schema::create('pages', function (Blueprint $table) {
            $table->id(); $table->string('slug', 60)->unique(); $table->string('title', 180);
            $table->text('body'); $table->boolean('is_published')->default(true); $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) { $table->string('key', 60)->primary(); $table->text('value')->nullable(); });
    }
    public function down(): void { Schema::dropIfExists('settings'); Schema::dropIfExists('pages'); Schema::dropIfExists('articles'); }
};
