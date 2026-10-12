<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('resource_links', function (Blueprint $table) {
        $table->id(); $table->string('title', 120); $table->string('description', 400)->nullable();
        $table->string('url', 2048); $table->boolean('is_published')->default(false);
        $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
    }); }
    public function down(): void { Schema::dropIfExists('resource_links'); }
};
