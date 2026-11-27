<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bengkels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->text('alamat');
            $table->string('telepon', 25);
            $table->string('link_maps')->nullable();
            $table->string('foto')->nullable();
            $table->time('jam_buka');
            $table->time('jam_tutup');
            $table->unsignedInteger('kuota_harian')->default(5);
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bengkels');
    }
};