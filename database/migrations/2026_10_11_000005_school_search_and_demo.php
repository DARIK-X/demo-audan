<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('schools', function (Blueprint $table) {
            $table->text('search_text')->nullable();
            $table->string('demo_key')->nullable()->unique();
        });
        Schema::table('articles', fn (Blueprint $table) => $table->string('demo_key')->nullable()->unique());
        DB::table('schools')->orderBy('id')->chunkById(100, function ($schools) {
            foreach ($schools as $school) DB::table('schools')->where('id', $school->id)->update(['search_text' => mb_strtolower($school->name.' '.$school->village, 'UTF-8')]);
        });
    }
    public function down(): void {
        Schema::table('schools', fn (Blueprint $table) => $table->dropColumn(['search_text', 'demo_key']));
        Schema::table('articles', fn (Blueprint $table) => $table->dropColumn('demo_key'));
    }
};
