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
		Schema::create('tournaments', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->onDelete('cascade');
			$table->foreignId('room_id')->constrained()->onDelete('cascade');
			$table->decimal('buyin', 10, 2);
			$table->date('date');
			$table->integer('place')->nullable();
			$table->decimal('cashout', 10, 2)->nullable();
			$table->integer('bounty_count')->default(0);
			$table->timestamps();

			$table->index('date');
			$table->index(['user_id', 'date']);
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
