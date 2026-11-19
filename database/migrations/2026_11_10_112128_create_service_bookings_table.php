<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Minimal booking record. The Booking module can extend this table later.
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laptop_id')->constrained()->cascadeOnDelete();
            $table->text('issue');
            $table->dateTime('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
