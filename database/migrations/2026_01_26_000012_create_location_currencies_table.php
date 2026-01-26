<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasTable('location_currencies')) {
			Schema::create('location_currencies', function (Blueprint $table) {
				$table->id();
				$table->foreignId('location_id')->constrained()->onDelete('cascade');
				$table->foreignId('currency_id')->constrained()->onDelete('cascade');
				$table->timestamps();

				$table->unique(['location_id', 'currency_id']);
				$table->index('location_id');
			});
		}
	}

	public function down(): void
	{
		Schema::dropIfExists('location_currencies');
	}
};
