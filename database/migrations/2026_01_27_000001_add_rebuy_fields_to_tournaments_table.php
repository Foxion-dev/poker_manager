<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('tournaments', function (Blueprint $table) {
			$table->integer('rebuy_count')->default(0)->after('bounty_count');
			$table->boolean('double_rebuy')->default(false)->after('rebuy_count');
		});
	}

	public function down(): void
	{
		Schema::table('tournaments', function (Blueprint $table) {
			$table->dropColumn(['rebuy_count', 'double_rebuy']);
		});
	}
};
