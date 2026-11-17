<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Vendor = a food outlet that sells menu items at one location. */
class Vendor extends Model
{
    protected $fillable = ['name', 'location_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /** The Vendor Manager accounts that run this vendor. */
    public function managers(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
