<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('location_tournaments', function (Blueprint $table) {
			$table->id();
			$table->foreignId('location_id')->constrained()->onDelete('cascade');
			$table->string('name');
			$table->decimal('buyin', 10, 2);
			$table->foreignId('currency_id')->nullable()->constrained()->onDelete('set null');
			$table->enum('format', ['classic', 'classic_bounty', 'progressive_bounty'])->default('classic');
			$table->decimal('itm_percentage', 5, 2)->default(15.00);
			$table->decimal('rake', 5, 2)->default(30.00);
			$table->enum('rake_type', ['fixed', 'percentage'])->default('fixed');
			$table->json('prize_distribution')->nullable();
			$table->date('date');
			$table->boolean('is_finished')->default(false);
			$table->timestamps();

			$table->index(['location_id', 'date']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('location_tournaments');
	}
};
