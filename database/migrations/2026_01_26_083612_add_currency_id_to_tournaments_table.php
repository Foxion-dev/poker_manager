<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('tournaments', function (Blueprint $table) {
			$table->foreignId('currency_id')->nullable()->after('buyin')->constrained()->onDelete('set null');
		});
	}

	public function down(): void
	{
		Schema::table('tournaments', function (Blueprint $table) {
			$table->dropForeign(['currency_id']);
			$table->dropColumn('currency_id');
		});
	}
};
