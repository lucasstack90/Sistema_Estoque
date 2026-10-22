<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $active = Product::where('active', true);

        $stats = [
            'products' => (clone $active)->count(),
            'units' => (int) (clone $active)->sum('stock'),
            'cost_value' => (float) (clone $active)->sum(DB::raw('stock * cost_price')),
            'sale_value' => (float) (clone $active)->sum(DB::raw('stock * sale_price')),
            'low' => (clone $active)->lowStock()->count(),
            'out' => (clone $active)->outOfStock()->count(),
        ];

        $start = Carbon::today()->subDays(29);
        $daily = StockMovement::query()
            ->where('created_at', '>=', $start)
            ->whereIn('type', [StockMovement::ENTRADA, StockMovement::SAIDA])
            ->selectRaw('DATE(created_at) as day, type, SUM(quantity) as total')
            ->groupBy(DB::raw('DATE(created_at)'), 'type')
            ->get();

        $chart = ['labels' => [], 'entradas' => [], 'saidas' => []];
        for ($d = $start->copy(); $d->lte(Carbon::today()); $d->addDay()) {
            $key = $d->toDateString();
            $chart['labels'][] = $d->format('d/m');
            $chart['entradas'][] = (int) $daily->where('day', $key)->where('type', StockMovement::ENTRADA)->sum('total');
            $chart['saidas'][] = (int) $daily->where('day', $key)->where('type', StockMovement::SAIDA)->sum('total');
        }

        $byCategory = Product::query()
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            ->selectRaw('categories.name as name, SUM(products.stock * products.cost_price) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $topSellers = StockMovement::query()
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->where('stock_movements.type', StockMovement::SAIDA)
            ->where('stock_movements.reason', 'Venda')
            ->where('stock_movements.created_at', '>=', $start)
            ->selectRaw('products.id, products.name, products.sku, SUM(stock_movements.quantity) as total')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $lowStock = Product::where('active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $latest = StockMovement::with(['product', 'user'])->latest('id')->limit(8)->get();

        return view('dashboard', compact('stats', 'chart', 'byCategory', 'topSellers', 'lowStock', 'latest'));
    }
}
