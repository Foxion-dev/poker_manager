<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('location_tournament_participants', function (Blueprint $table) {
			$table->id();
			$table->foreignId('location_tournament_id')->constrained()->onDelete('cascade');
			$table->string('name')->nullable();
			$table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
			$table->integer('place');
			$table->integer('rebuy')->default(0);
			$table->boolean('addon')->default(false);
			$table->boolean('is_paid')->default(false);
			$table->decimal('prize', 10, 2)->nullable();
			$table->timestamps();

			$table->unique(['location_tournament_id', 'user_id'], 'loc_tourn_part_unique');
			$table->index(['location_tournament_id', 'place'], 'loc_tourn_part_place_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('location_tournament_participants');
	}
};
