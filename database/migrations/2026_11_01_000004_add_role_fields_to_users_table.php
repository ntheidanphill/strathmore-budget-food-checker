<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * USERS: Laravel already creates a users table. We ADD the fields we need:
 *   username  - staff log in with a username + password (R2)
 *   role      - 'admin' or 'vendor_manager' (students are guests, no account)
 *   vendor_id - which vendor a Vendor Manager belongs to (NULL for admins)
 *   is_active - lets the Admin deactivate an account (R4)
 * Passwords stay hashed by Laravel (R5) - we never touch the password column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('name');
            $table->string('role', 20)->default('vendor_manager')->after('password');
            $table->foreignId('vendor_id')->nullable()->after('role')
                  ->constrained('vendors')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id'); // removes FK + column together
            $table->dropColumn(['username', 'role', 'is_active']);
        });
    }
};
