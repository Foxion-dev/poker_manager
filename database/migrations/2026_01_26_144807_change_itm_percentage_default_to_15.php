<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		DB::statement('ALTER TABLE location_tournaments MODIFY COLUMN itm_percentage DECIMAL(5,2) DEFAULT 15.00');
	}

	public function down(): void
	{
		DB::statement('ALTER TABLE location_tournaments MODIFY COLUMN itm_percentage DECIMAL(5,2) DEFAULT 100.00');
	}
};
