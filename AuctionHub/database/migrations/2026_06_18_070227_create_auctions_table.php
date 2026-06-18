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
    Schema::create('auctions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description');
        $table->timestamp('starts_at');
        $table->timestamp('ends_at');
        $table->decimal('reserve_price', 12, 2);
        $table->decimal('current_price', 12, 2);
        $table->decimal('bid_increment', 12, 2);
        $table->enum('status', ['draft', 'scheduled', 'live', 'ended', 'cancelled']);

        // Virtual column for is_live (computed in SQL)
        $table->virtualAs(
            "CASE WHEN status = 'live' AND NOW() BETWEEN starts_at AND ends_at THEN 1 ELSE 0 END",
            'is_live'
        );

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
