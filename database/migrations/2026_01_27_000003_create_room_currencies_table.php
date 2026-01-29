<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('room_currencies', function (Blueprint $table) {
			$table->id();
			$table->foreignId('room_id')->constrained()->onDelete('cascade');
			$table->foreignId('currency_id')->constrained()->onDelete('cascade');
			$table->timestamps();

			$table->unique(['room_id', 'currency_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('room_currencies');
	}
};
