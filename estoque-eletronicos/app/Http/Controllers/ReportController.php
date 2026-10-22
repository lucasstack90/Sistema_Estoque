<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function products(Request $request): StreamedResponse
    {
        $query = Product::with(['category', 'supplier'])
            ->filter($request->only(['q', 'category_id', 'supplier_id', 'status']))
            ->orderBy('name');

        return $this->csv('produtos_'.now()->format('Y-m-d_His').'.csv', [
            'SKU', 'Produto', 'Marca', 'Modelo', 'Categoria', 'Fornecedor', 'Estoque', 'Estoque mínimo',
            'Situação', 'Custo unitário', 'Preço de venda', 'Valor em estoque (custo)', 'Localização', 'Ativo',
        ], function ($out) use ($query) {
            $query->chunk(500, function ($products) use ($out) {
                foreach ($products as $p) {
                    fputcsv($out, [
                        $p->sku, $p->name, $p->brand, $p->model, $p->category?->name, $p->supplier?->name,
                        $p->stock, $p->min_stock,
                        ['sem_estoque' => 'Sem estoque', 'baixo' => 'Estoque baixo', 'ok' => 'OK'][$p->stockStatus()],
                        $this->num($p->cost_price), $this->num($p->sale_price), $this->num($p->stock * (float) $p->cost_price),
                        $p->location, $p->active ? 'Sim' : 'Não',
                    ], ';');
                }
            });
        });
    }

    public function movements(Request $request): StreamedResponse
    {
        $query = StockMovement::with(['product', 'user'])
            ->filter($request->only(['type', 'product_id', 'date_from', 'date_to', 'q']))
            ->latest('id');

        return $this->csv('movimentacoes_'.now()->format('Y-m-d_His').'.csv', [
            'Data', 'Tipo', 'Motivo', 'SKU', 'Produto', 'Quantidade', 'Saldo anterior', 'Saldo posterior',
            'Custo unitário', 'Documento', 'Observações', 'Usuário',
        ], function ($out) use ($query) {
            $query->chunk(500, function ($movements) use ($out) {
                foreach ($movements as $m) {
                    fputcsv($out, [
                        $m->created_at->format('d/m/Y H:i'), $m->typeLabel(), $m->reason, $m->product?->sku,
                        $m->product?->name, $m->signedQuantity(), $m->balance_before, $m->balance_after,
                        $m->unit_cost !== null ? $this->num($m->unit_cost) : '', $m->document, $m->notes, $m->user?->name,
                    ], ';');
                }
            });
        });
    }

    private function csv(string $filename, array $header, callable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');
            $rows($out);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function num(float|string $value): string
    {
        return number_format((float) $value, 2, ',', '');
    }
}
