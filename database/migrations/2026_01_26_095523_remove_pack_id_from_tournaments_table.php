<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
	public function up(): void
	{
		if (Schema::hasColumn('tournaments', 'pack_id')) {
			try {
				DB::statement('ALTER TABLE tournaments DROP FOREIGN KEY tournaments_pack_id_foreign');
			} catch (\Exception $e) {
			}
			Schema::table('tournaments', function (Blueprint $table) {
				$table->dropColumn('pack_id');
			});
		}
	}

	public function down(): void
	{
		Schema::table('tournaments', function (Blueprint $table) {
			$table->foreignId('pack_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
			$table->index('pack_id');
		});
	}
};
