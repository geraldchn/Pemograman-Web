<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            $table->unsignedSmallInteger('purchase_year')->nullable()->after('serial_number');
            $table->string('processor', 100)->nullable()->after('purchase_year');
            $table->unsignedSmallInteger('ram_gb')->nullable()->after('processor');
            $table->string('storage', 100)->nullable()->after('ram_gb');
            $table->string('gpu', 100)->nullable()->after('storage');
            $table->decimal('screen_size', 3, 1)->nullable()->after('gpu');
            $table->string('os', 60)->nullable()->after('screen_size');
            $table->text('notes')->nullable()->after('os');
            $table->string('photo')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            $table->dropColumn(['purchase_year', 'processor', 'ram_gb', 'storage', 'gpu', 'screen_size', 'os', 'notes', 'photo']);
        });
    }
};
