<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per progress update on a booking (the timeline).
        Schema::create('service_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // admin who wrote it
            $table->string('status', 30);
            $table->text('note');
            $table->timestamps();

            $table->index(['service_booking_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_updates');
    }
};
