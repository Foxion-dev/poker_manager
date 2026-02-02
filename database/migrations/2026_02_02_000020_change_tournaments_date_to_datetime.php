<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		$driver = Schema::getConnection()->getDriverName();
		if ($driver === 'mysql') {
			DB::statement('ALTER TABLE tournaments MODIFY date DATETIME NOT NULL');
		} elseif ($driver === 'sqlite') {
			DB::statement('ALTER TABLE tournaments RENAME COLUMN date TO date_old');
			Schema::table('tournaments', function (Blueprint $table) {
				$table->dateTime('date')->nullable(false)->after('currency_id');
			});
			DB::statement("UPDATE tournaments SET date = datetime(date_old)");
			Schema::table('tournaments', function (Blueprint $table) {
				$table->dropColumn('date_old');
			});
		}
	}

	public function down(): void
	{
		$driver = Schema::getConnection()->getDriverName();
		if ($driver === 'mysql') {
			DB::statement('ALTER TABLE tournaments MODIFY date DATE NOT NULL');
		} elseif ($driver === 'sqlite') {
			DB::statement('ALTER TABLE tournaments RENAME COLUMN date TO date_old');
			Schema::table('tournaments', function (Blueprint $table) {
				$table->date('date')->nullable(false)->after('currency_id');
			});
			DB::statement("UPDATE tournaments SET date = date(date_old)");
			Schema::table('tournaments', function (Blueprint $table) {
				$table->dropColumn('date_old');
			});
		}
	}
};
