<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LOCATIONS: the three campus food areas (Student Centre, Siwaka, Madaraka).
 * is_active lets the Admin "deactivate" a location instead of deleting it (R4).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();                                  // primary key
            $table->string('name', 80)->unique();          // e.g. "Siwaka"
            $table->boolean('is_active')->default(true);   // false = hidden from students
            $table->timestamps();                          // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
