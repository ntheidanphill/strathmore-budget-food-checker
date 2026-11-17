<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Category = type of food (Breakfast, Lunch, Snacks, Drinks...). */
class Category extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Accessor: $category->emoji gives a food icon for the cards.
     * Falls back to a plate for any category the Admin adds later.
     */
    public function getEmojiAttribute(): string
    {
        return match (strtolower($this->name)) {
            'breakfast' => '🍳',
            'lunch'     => '🍛',
            'snacks'    => '🥟',
            'drinks'    => '🥤',
            'fast food' => '🍟',
            default     => '🍽️',
        };
    }
}
