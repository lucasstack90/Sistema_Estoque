<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sku', 'name', 'brand', 'model', 'category_id', 'supplier_id', 'description',
    'cost_price', 'sale_price', 'min_stock', 'location', 'warranty_months', 'active',
])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'warranty_months' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock', 0);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $q->where(fn (Builder $w) => $w
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%"));
            })
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
            ->when($filters['supplier_id'] ?? null, fn (Builder $q, $id) => $q->where('supplier_id', $id))
            ->when($filters['status'] ?? null, function (Builder $q, string $status) {
                match ($status) {
                    'baixo' => $q->lowStock(),
                    'sem_estoque' => $q->outOfStock(),
                    'ativos' => $q->where('active', true),
                    'inativos' => $q->where('active', false),
                    default => null,
                };
            });
    }

    public function stockStatus(): string
    {
        return match (true) {
            $this->stock === 0 => 'sem_estoque',
            $this->stock <= $this->min_stock => 'baixo',
            default => 'ok',
        };
    }

    public function marginPercent(): ?float
    {
        if ((float) $this->sale_price <= 0) {
            return null;
        }

        return ((float) $this->sale_price - (float) $this->cost_price) / (float) $this->sale_price * 100;
    }
}
