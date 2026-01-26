<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('location_users', function (Blueprint $table) {
			$table->string('name')->nullable()->after('location_id');
		});

		DB::statement('ALTER TABLE location_users MODIFY user_id BIGINT UNSIGNED NULL');

		try {
			DB::statement('ALTER TABLE location_users DROP INDEX location_users_location_id_user_id_unique');
		} catch (\Exception $e) {
		}

		DB::statement('ALTER TABLE location_users ADD UNIQUE KEY location_users_location_id_user_id_unique (location_id, user_id)');
	}

	public function down(): void
	{
		Schema::table('location_users', function (Blueprint $table) {
			$table->dropColumn('name');
		});

		DB::statement('ALTER TABLE location_users MODIFY user_id BIGINT UNSIGNED NOT NULL');
	}
};
