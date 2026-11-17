<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User = a staff member who can log in (Admin or Vendor Manager).
 * Students are guests and have NO row in this table.
 * This file REPLACES the default app/Models/User.php.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Role names are stored as constants so we never mistype a string.
    public const ROLE_ADMIN = 'admin';
    public const ROLE_VENDOR = 'vendor_manager';

    // Columns allowed for mass assignment, e.g. User::create([...])
    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'vendor_id', 'is_active'];

    // Never include these when the model is converted to JSON/array
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // Laravel auto-hashes the password on save (R5)
            'is_active'         => 'boolean',
        ];
    }

    /** A vendor manager belongs to one vendor. */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isVendorManager(): bool
    {
        return $this->role === self::ROLE_VENDOR;
    }
}
