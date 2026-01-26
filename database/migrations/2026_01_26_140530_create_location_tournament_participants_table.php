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
		if (!Schema::hasTable('location_tournament_participants')) {
			Schema::create('location_tournament_participants', function (Blueprint $table) {
				$table->id();
				$table->foreignId('location_tournament_id')->constrained()->onDelete('cascade');
				$table->foreignId('user_id')->constrained()->onDelete('cascade');
				$table->integer('place');
				$table->decimal('prize', 10, 2)->nullable();
				$table->timestamps();

			$table->unique(['location_tournament_id', 'user_id'], 'loc_tourn_part_unique');
			$table->index(['location_tournament_id', 'place']);
			});
		}
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_tournament_participants');
    }
};
