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
        $table->timestamp('starts_at')->nullable();
        $table->timestamp('ends_at')->nullable();
        $table->decimal('reserve_price', 12, 2);
        $table->decimal('starting_price', 12, 2)->nullable();
        $table->decimal('current_price', 12, 2)->nullable();
        $table->decimal('bid_increment', 12, 2);
        $table->enum('status', ['draft', 'scheduled', 'live', 'ended', 'cancelled']);
        // Derived/computed columns like `is_live` or generated `current_price`
        // can be managed at the application/model level or defined using
        // DB-specific generated columns. For compatibility and simplicity
        // we store `is_live` as a boolean and update it in application logic.
        $table->boolean('is_live')->default(false);

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
