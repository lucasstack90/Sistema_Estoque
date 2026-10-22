<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'product_id', 'date_from', 'date_to', 'q']);

        $movements = StockMovement::with(['product', 'user'])
            ->filter($filters)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('movements.index', [
            'movements' => $movements,
            'filters' => $filters,
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function create(Request $request)
    {
        return view('movements.create', [
            'products' => Product::with('category')->where('active', true)->orderBy('name')->get(),
            'selectedProduct' => $request->integer('product_id') ?: null,
            'selectedType' => array_key_exists($request->type, StockMovement::TYPES) ? $request->type : StockMovement::ENTRADA,
        ]);
    }

    public function store(Request $request, StockService $stock)
    {
        $request->merge(['unit_cost' => parse_money($request->input('unit_cost'))]);
        $type = $request->input('type');

        $data = $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->where('active', true)],
            'type' => ['required', Rule::in(array_keys(StockMovement::TYPES))],
            'reason' => ['required', Rule::in(StockMovement::REASONS[$type] ?? [])],
            'quantity' => ['required', 'integer', $type === StockMovement::AJUSTE ? 'min:0' : 'min:1', 'max:1000000'],
            'unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'document' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:255'],
            'redirect_to_product' => ['nullable', 'boolean'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $movement = $stock->register(
            $product,
            $data['type'],
            (int) $data['quantity'],
            $data['reason'],
            $request->user(),
            $data['type'] === StockMovement::ENTRADA && isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            $data['document'] ?? null,
            $data['notes'] ?? null,
        );

        $message = "{$movement->typeLabel()} registrada: {$product->name}. Saldo: {$movement->balance_before} → {$movement->balance_after}.";
        if ($product->stock <= $product->min_stock) {
            $message .= ' Atenção: produto abaixo do estoque mínimo!';
        }

        $route = $request->boolean('redirect_to_product')
            ? route('products.show', $product)
            : route('movements.index');

        return redirect($route)->with('success', $message);
    }
}
