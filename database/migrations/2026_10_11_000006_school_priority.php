<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('students')->nullable()->default(null)->change();
        });
    }
    public function down(): void {
        Schema::table('schools', fn (Blueprint $table) => $table->dropColumn('is_featured'));
        // Nullable counts stay nullable to preserve unspecified student counts.
    }
};
