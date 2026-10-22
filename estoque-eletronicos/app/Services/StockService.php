<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Registra uma movimentação e atualiza o saldo do produto de forma atômica.
     * Para "ajuste", $quantity é o saldo contado (novo saldo absoluto).
     */
    public function register(
        Product $product,
        string $type,
        int $quantity,
        string $reason,
        ?User $user = null,
        ?float $unitCost = null,
        ?string $document = null,
        ?string $notes = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $type, $quantity, $reason, $user, $unitCost, $document, $notes) {
            $locked = Product::whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            $before = $locked->stock;

            $after = match ($type) {
                StockMovement::ENTRADA => $before + $quantity,
                StockMovement::SAIDA => $before - $quantity,
                StockMovement::AJUSTE => $quantity,
                default => throw ValidationException::withMessages(['type' => 'Tipo de movimentação inválido.']),
            };

            if ($type !== StockMovement::AJUSTE && $quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => 'A quantidade deve ser maior que zero.']);
            }

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Estoque insuficiente. Saldo atual de {$locked->name}: {$before} unidade(s).",
                ]);
            }

            if ($type === StockMovement::AJUSTE && $after === $before) {
                throw ValidationException::withMessages(['quantity' => 'O saldo informado é igual ao saldo atual; nada a ajustar.']);
            }

            if ($type === StockMovement::ENTRADA && $unitCost !== null && $unitCost > 0) {
                $locked->cost_price = round(
                    (($before * (float) $locked->cost_price) + ($quantity * $unitCost)) / $after,
                    2
                );
            }

            $locked->stock = $after;
            $locked->save();

            $product->setRawAttributes($locked->getAttributes(), true);

            return StockMovement::create([
                'product_id' => $locked->id,
                'user_id' => $user?->id,
                'type' => $type,
                'reason' => $reason,
                'quantity' => abs($after - $before),
                'balance_before' => $before,
                'balance_after' => $after,
                'unit_cost' => $unitCost,
                'document' => $document,
                'notes' => $notes,
            ]);
        });
    }
}
