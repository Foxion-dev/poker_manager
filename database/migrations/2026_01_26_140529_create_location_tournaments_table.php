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
		if (!Schema::hasTable('location_tournaments')) {
			Schema::create('location_tournaments', function (Blueprint $table) {
				$table->id();
				$table->foreignId('location_id')->constrained()->onDelete('cascade');
				$table->string('name');
				$table->decimal('buyin', 10, 2);
				$table->enum('format', ['classic', 'classic_bounty', 'progressive_bounty'])->default('classic');
				$table->date('date');
				$table->timestamps();

				$table->index(['location_id', 'date']);
			});
		}
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_tournaments');
    }
};
