<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('schools', function (Blueprint $table) {
            $table->id(); $table->string('name', 160); $table->string('type', 80)->index();
            $table->string('village', 100)->index(); $table->string('address', 200);
            $table->unsignedInteger('students')->default(0); $table->string('phone', 40)->nullable();
            $table->string('website_url', 2048)->nullable(); $table->string('photo_path')->nullable();
            $table->text('description')->nullable(); $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('schools'); }
};
