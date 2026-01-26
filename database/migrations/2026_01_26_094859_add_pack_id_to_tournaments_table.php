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
		Schema::table('tournaments', function (Blueprint $table) {
			if (!Schema::hasColumn('tournaments', 'pack_id')) {
				$table->foreignId('pack_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
				$table->index('pack_id');
			}
		});
	}

	public function down(): void
	{
		Schema::table('tournaments', function (Blueprint $table) {
			$table->dropForeign(['pack_id']);
			$table->dropColumn('pack_id');
		});
	}
};
