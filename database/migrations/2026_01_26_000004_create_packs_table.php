<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('packs', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->onDelete('cascade');
			$table->string('name');
			$table->date('start_date');
			$table->date('end_date')->nullable();
			$table->decimal('buyin', 10, 2);
			$table->decimal('cashout', 10, 2)->nullable();
			$table->foreignId('currency_id')->nullable()->constrained()->onDelete('set null');
			$table->text('description')->nullable();
			$table->timestamps();

			$table->index(['user_id', 'start_date']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('packs');
	}
};
