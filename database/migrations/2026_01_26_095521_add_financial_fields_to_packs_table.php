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
		Schema::table('packs', function (Blueprint $table) {
			$table->decimal('buyin', 10, 2)->after('end_date');
			$table->decimal('cashout', 10, 2)->nullable()->after('buyin');
			$table->foreignId('currency_id')->nullable()->after('cashout')->constrained()->onDelete('set null');
		});
	}

	public function down(): void
	{
		Schema::table('packs', function (Blueprint $table) {
			$table->dropForeign(['currency_id']);
			$table->dropColumn(['buyin', 'cashout', 'currency_id']);
		});
	}
};
