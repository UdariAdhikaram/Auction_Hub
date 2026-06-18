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
    Schema::create('bids', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('auction_id')->constrained()->onDelete('cascade');
        $table->decimal('amount', 12, 2);
        $table->timestamp('placed_at');
        $table->timestamps();

        // Unique constraint to prevent duplicate identical bids
        $table->unique(['user_id', 'auction_id', 'amount']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
