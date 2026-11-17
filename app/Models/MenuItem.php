<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MenuItem = one food item with a price.
 *
 * The two query scopes below REPLACE the old PostgreSQL function
 * GetItemsByBudget from our first project plan. A scope is a reusable
 * piece of query:  MenuItem::active()->withinBudget(200)->get()
 */
class MenuItem extends Model
{
    protected $fillable = ['vendor_id', 'category_id', 'name', 'price', 'is_active'];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * BUSINESS RULE (R1): students only ever see items that are in stock
     * AND whose vendor AND location are both active.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('vendor', function (Builder $vendor) {
                $vendor->where('is_active', true)
                    ->whereHas('location', fn (Builder $loc) => $loc->where('is_active', true));
            });
    }

    /** Only items priced LESS THAN OR EQUAL TO the student's budget (R1). */
    public function scopeWithinBudget(Builder $query, float $budget): Builder
    {
        return $query->where('price', '<=', $budget);
    }
}
