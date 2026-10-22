<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id', 'user_id', 'type', 'reason', 'quantity', 'balance_before',
    'balance_after', 'unit_cost', 'document', 'notes',
])]
class StockMovement extends Model
{
    public const ENTRADA = 'entrada';
    public const SAIDA = 'saida';
    public const AJUSTE = 'ajuste';

    public const TYPES = [
        self::ENTRADA => 'Entrada',
        self::SAIDA => 'Saída',
        self::AJUSTE => 'Ajuste',
    ];

    public const REASONS = [
        self::ENTRADA => ['Compra de fornecedor', 'Devolução de cliente', 'Troca em garantia', 'Outros'],
        self::SAIDA => ['Venda', 'Avaria / defeito', 'Devolução ao fornecedor', 'Envio para assistência técnica', 'Uso interno', 'Outros'],
        self::AJUSTE => ['Inventário', 'Correção de lançamento', 'Outros'],
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['type'] ?? null, fn (Builder $q, $type) => $q->where('type', $type))
            ->when($filters['product_id'] ?? null, fn (Builder $q, $id) => $q->where('product_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $q->where(fn (Builder $w) => $w
                    ->where('document', 'like', "%{$term}%")
                    ->orWhereHas('product', fn (Builder $p) => $p
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")));
            });
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function signedQuantity(): int
    {
        return $this->balance_after - $this->balance_before;
    }
}
