<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('telegram_bot_settings', function (Blueprint $table) {
			$table->id();
			$table->text('bot_token')->nullable();
			$table->boolean('is_enabled')->default(false);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('telegram_bot_settings');
	}
};
