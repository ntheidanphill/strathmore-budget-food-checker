<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Location = a campus food area (Student Centre, Siwaka, Madaraka). */
class Location extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** One location has many vendors. */
    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }
}
