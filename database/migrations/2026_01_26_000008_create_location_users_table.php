<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('location_users', function (Blueprint $table) {
			$table->id();
			$table->foreignId('location_id')->constrained()->onDelete('cascade');
			$table->string('name')->nullable();
			$table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
			$table->timestamps();

			$table->unique(['location_id', 'user_id'], 'location_users_location_id_user_id_unique');
			$table->index('location_id');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('location_users');
	}
};
