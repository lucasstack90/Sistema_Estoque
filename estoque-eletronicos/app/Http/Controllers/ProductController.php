<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private const SORTS = [
        'name' => ['name', 'asc'],
        'stock_asc' => ['stock', 'asc'],
        'stock_desc' => ['stock', 'desc'],
        'price_desc' => ['sale_price', 'desc'],
        'recent' => ['created_at', 'desc'],
    ];

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'category_id', 'supplier_id', 'status', 'sort']);
        [$column, $direction] = self::SORTS[$filters['sort'] ?? 'name'] ?? self::SORTS['name'];

        $products = Product::with('category')
            ->filter($filters)
            ->orderBy($column, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('products.form', $this->formData(new Product(['active' => true, 'min_stock' => 5])));
    }

    public function store(ProductRequest $request, StockService $stock)
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($data, $request, $stock) {
            $product = Product::create($data);
            if (! empty($data['initial_stock'])) {
                $stock->register($product, 'entrada', (int) $data['initial_stock'], 'Estoque inicial', $request->user());
            }

            return $product;
        });

        return redirect()->route('products.show', $product)->with('success', 'Produto cadastrado com sucesso.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'supplier']);
        $movements = $product->movements()->with('user')->latest('id')->paginate(10);

        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        return view('products.form', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $product)
    {
        if ($product->movements()->exists()) {
            return back()->with('error', 'Este produto possui movimentações e não pode ser excluído. Desative-o para que não apareça mais nas operações.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produto excluído.');
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ];
    }
}
