<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tournaments', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->onDelete('cascade');
			$table->foreignId('room_id')->constrained()->onDelete('cascade');
			$table->decimal('buyin', 10, 2);
			$table->foreignId('currency_id')->nullable()->constrained()->onDelete('set null');
			$table->date('date');
			$table->integer('place')->nullable();
			$table->decimal('cashout', 10, 2)->nullable();
			$table->integer('bounty_count')->default(0);
			$table->integer('players_count')->nullable();
			$table->timestamps();

			$table->index('date');
			$table->index(['user_id', 'date']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tournaments');
	}
};
