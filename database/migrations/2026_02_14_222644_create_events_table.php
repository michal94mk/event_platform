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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('slug')->unique();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('venue_name');
            $table->string('venue_address')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_country')->nullable();
            $table->decimal('venue_latitude', 10, 8)->nullable();
            $table->decimal('venue_longitude', 11, 8)->nullable();
            $table->unsignedInteger('max_attendees')->nullable();
            $table->decimal('ticket_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('status', 20)->default('draft');
            $table->string('cover_image')->nullable();
            $table->string('google_calendar_event_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
