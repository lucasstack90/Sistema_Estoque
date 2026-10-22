<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(StockService $stock): void
    {
        mt_srand(2026);

        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@estoque.com',
            'password' => 'admin123',
            'role' => 'admin',
        ]);
        $operador = User::create([
            'name' => 'Operador de Estoque',
            'email' => 'operador@estoque.com',
            'password' => 'operador123',
            'role' => 'operador',
        ]);

        $categories = collect([
            'Smartphones' => 'Celulares e smartphones',
            'Notebooks' => 'Notebooks e ultrabooks',
            'Tablets' => 'Tablets e e-readers',
            'Áudio' => 'Fones de ouvido, caixas de som e soundbars',
            'TVs e Vídeo' => 'Smart TVs, projetores e streaming',
            'Games' => 'Consoles, controles e acessórios gamer',
            'Smartwatches' => 'Relógios inteligentes e pulseiras fitness',
            'Acessórios' => 'Carregadores, cabos, capas e periféricos',
        ])->map(fn ($desc, $name) => Category::create(['name' => $name, 'description' => $desc]));

        $suppliers = collect([
            ['name' => 'TechDistribuidora Ltda', 'cnpj' => '12.345.678/0001-90', 'contact_name' => 'Marcos Pereira', 'email' => 'vendas@techdistribuidora.com.br', 'phone' => '(11) 3456-7890'],
            ['name' => 'Eletro Atacado Brasil S.A.', 'cnpj' => '23.456.789/0001-01', 'contact_name' => 'Fernanda Costa', 'email' => 'comercial@eletroatacado.com.br', 'phone' => '(21) 2345-6789'],
            ['name' => 'Conecta Importadora', 'cnpj' => '34.567.890/0001-12', 'contact_name' => 'Ricardo Alves', 'email' => 'pedidos@conectaimport.com.br', 'phone' => '(41) 3234-5678'],
            ['name' => 'GameZone Distribuição', 'cnpj' => '45.678.901/0001-23', 'contact_name' => 'Juliana Rocha', 'email' => 'contato@gamezonedist.com.br', 'phone' => '(31) 3123-4567'],
        ])->map(fn ($s) => Supplier::create($s));

        // [sku, nome, marca, modelo, categoria, fornecedor(idx), custo, venda, estoque inicial, mínimo, garantia, repõe?]
        $catalog = [
            ['SMP-SAM-A55', 'Smartphone Samsung Galaxy A55 5G 128GB', 'Samsung', 'SM-A556E', 'Smartphones', 0, 1650.00, 2199.00, 40, 10, 12, true],
            ['SMP-SAM-S24', 'Smartphone Samsung Galaxy S24 256GB', 'Samsung', 'SM-S921B', 'Smartphones', 0, 3900.00, 4999.00, 15, 5, 12, true],
            ['SMP-APL-IP15', 'iPhone 15 128GB', 'Apple', 'A3090', 'Smartphones', 1, 4800.00, 5999.00, 12, 4, 12, false],
            ['SMP-XIA-RN13', 'Smartphone Xiaomi Redmi Note 13 256GB', 'Xiaomi', '23129RAA4G', 'Smartphones', 2, 1100.00, 1499.00, 35, 10, 12, true],
            ['SMP-MOT-G84', 'Smartphone Motorola Moto G84 5G 256GB', 'Motorola', 'XT2347', 'Smartphones', 0, 1050.00, 1399.00, 30, 8, 12, true],
            ['NTB-DEL-I15', 'Notebook Dell Inspiron 15 i5 16GB 512GB SSD', 'Dell', 'I15-3520', 'Notebooks', 1, 3200.00, 3999.00, 10, 4, 12, true],
            ['NTB-LEN-IP3', 'Notebook Lenovo IdeaPad 3 Ryzen 5 8GB 256GB', 'Lenovo', '15ALC6', 'Notebooks', 1, 2300.00, 2899.00, 14, 5, 12, false],
            ['NTB-APL-MBA', 'MacBook Air 13" M2 8GB 256GB', 'Apple', 'MLY33BZ/A', 'Notebooks', 1, 6500.00, 7999.00, 6, 3, 12, true],
            ['NTB-ACE-NIT', 'Notebook Gamer Acer Nitro V15 i5 RTX 3050', 'Acer', 'ANV15-51', 'Notebooks', 3, 4100.00, 4999.00, 8, 3, 12, true],
            ['TAB-SAM-A9', 'Tablet Samsung Galaxy Tab A9+ 64GB Wi-Fi', 'Samsung', 'SM-X210', 'Tablets', 0, 850.00, 1199.00, 20, 6, 12, true],
            ['TAB-APL-IPD', 'iPad 10ª geração 64GB Wi-Fi', 'Apple', 'A2696', 'Tablets', 1, 2600.00, 3299.00, 9, 3, 12, true],
            ['TAB-AMZ-KDL', 'Kindle 11ª geração 16GB', 'Amazon', 'C2V2L3', 'Tablets', 2, 420.00, 599.00, 25, 8, 12, true],
            ['AUD-JBL-T520', 'Fone de Ouvido Bluetooth JBL Tune 520BT', 'JBL', 'T520BT', 'Áudio', 2, 160.00, 249.00, 50, 15, 12, true],
            ['AUD-APL-APP', 'AirPods Pro 2ª geração USB-C', 'Apple', 'MTJV3', 'Áudio', 1, 1300.00, 1799.00, 18, 6, 12, true],
            ['AUD-JBL-FL6', 'Caixa de Som JBL Flip 6', 'JBL', 'FLIP6', 'Áudio', 2, 520.00, 749.00, 22, 8, 12, false],
            ['AUD-SON-WH1', 'Headphone Sony WH-1000XM5', 'Sony', 'WH-1000XM5', 'Áudio', 2, 1700.00, 2299.00, 7, 3, 12, true],
            ['TVV-SAM-55C', 'Smart TV Samsung 55" Crystal UHD 4K', 'Samsung', 'UN55CU7700', 'TVs e Vídeo', 0, 2100.00, 2799.00, 10, 4, 12, true],
            ['TVV-LG-50UR', 'Smart TV LG 50" 4K UHD ThinQ AI', 'LG', '50UR8750', 'TVs e Vídeo', 0, 1800.00, 2399.00, 12, 4, 12, true],
            ['TVV-AMZ-FTV', 'Fire TV Stick 4K com Alexa', 'Amazon', 'B0BP9SNVH9', 'TVs e Vídeo', 2, 230.00, 379.00, 40, 12, 12, true],
            ['GAM-SON-PS5', 'Console PlayStation 5 Slim 1TB', 'Sony', 'CFI-2014', 'Games', 3, 3300.00, 3999.00, 10, 4, 12, true],
            ['GAM-MSF-XSS', 'Console Xbox Series S 512GB', 'Microsoft', 'RRS-00006', 'Games', 3, 2000.00, 2599.00, 9, 3, 12, true],
            ['GAM-SON-DS5', 'Controle sem fio DualSense PS5', 'Sony', 'CFI-ZCT1W', 'Games', 3, 330.00, 449.00, 30, 10, 3, true],
            ['SWT-APL-SE2', 'Apple Watch SE 2ª geração 40mm GPS', 'Apple', 'MR9U3', 'Smartwatches', 1, 1700.00, 2199.00, 8, 3, 12, true],
            ['SWT-XIA-SB8', 'Smartband Xiaomi Mi Band 8', 'Xiaomi', 'M2239B1', 'Smartwatches', 2, 170.00, 279.00, 45, 15, 12, true],
            ['ACS-APL-20W', 'Carregador Apple USB-C 20W', 'Apple', 'MHJA3BZ/A', 'Acessórios', 1, 110.00, 179.00, 60, 20, 12, true],
            ['ACS-ANK-PB10', 'Power Bank Anker 10000mAh PowerCore', 'Anker', 'A1263', 'Acessórios', 2, 120.00, 199.00, 40, 12, 18, true],
            ['ACS-LOG-MXM', 'Mouse sem fio Logitech MX Master 3S', 'Logitech', '910-006561', 'Acessórios', 2, 480.00, 649.00, 15, 5, 12, false],
            ['ACS-USB-C2M', 'Cabo USB-C para USB-C 2m 60W', 'Baseus', 'CATKLF-H91', 'Acessórios', 2, 25.00, 59.00, 100, 30, 3, true],
        ];

        $locations = ['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'C1', 'C2', 'D1'];
        $products = [];
        $realNow = Carbon::now();
        $start = $realNow->copy()->startOfDay()->subDays(35)->setTime(9, 0);

        foreach ($catalog as $i => [$sku, $name, $brand, $model, $cat, $sup, $cost, $sale, $initial, $min, $warranty, $restock]) {
            $product = Product::create([
                'sku' => $sku,
                'name' => $name,
                'brand' => $brand,
                'model' => $model,
                'category_id' => $categories[$cat]->id,
                'supplier_id' => $suppliers[$sup]->id,
                'cost_price' => $cost,
                'sale_price' => $sale,
                'min_stock' => $min,
                'location' => 'Corredor '.$locations[$i % count($locations)],
                'warranty_months' => $warranty,
                'active' => true,
            ]);
            Carbon::setTestNow($start->copy()->addMinutes($i * 7));
            $stock->register($product, StockMovement::ENTRADA, $initial, 'Estoque inicial', $admin, $cost, 'NF-'.(1000 + $i));
            $products[] = [$product, $restock, $cost];
        }

        $nf = 2000;
        $pedido = 50000;
        for ($day = 30; $day >= 0; $day--) {
            foreach ($products as [$product, $restock, $cost]) {
                $product->refresh();
                $when = $realNow->copy()->startOfDay()->subDays($day)->setTime(mt_rand(8, 17), mt_rand(0, 59));
                if ($when->greaterThan($realNow)) {
                    $when = $realNow->copy()->subMinutes(mt_rand(5, 120));
                }
                Carbon::setTestNow($when);

                if ($product->stock > 0 && mt_rand(1, 100) <= 45) {
                    $qty = min($product->stock, mt_rand(1, max(1, intdiv($product->min_stock, 3))));
                    $stock->register($product, StockMovement::SAIDA, $qty, 'Venda', $operador, null, 'PED-'.$pedido++);
                }

                if ($product->stock > 0 && mt_rand(1, 100) <= 2) {
                    $stock->register($product, StockMovement::SAIDA, 1, 'Avaria / defeito', $operador, null, null, 'Produto com defeito de fábrica');
                }

                $product->refresh();
                if ($restock && $product->stock <= $product->min_stock && $day > 2 && mt_rand(1, 100) <= 60) {
                    $qty = $product->min_stock * 2;
                    $unit = round($cost * (mt_rand(97, 104) / 100), 2);
                    $stock->register($product, StockMovement::ENTRADA, $qty, 'Compra de fornecedor', $admin, $unit, 'NF-'.$nf++);
                }
            }
        }

        Carbon::setTestNow($realNow->copy()->subMinutes(3));
        $fone = $products[12][0]->refresh();
        $stock->register($fone, StockMovement::AJUSTE, max(0, $fone->stock - 2), 'Inventário', $admin, null, null, 'Contagem física mensal');

        Carbon::setTestNow();

        Product::create([
            'sku' => 'ACS-OLD-MICROUSB',
            'name' => 'Cabo Micro USB 1m (descontinuado)',
            'brand' => 'Genérico',
            'category_id' => $categories['Acessórios']->id,
            'cost_price' => 5,
            'sale_price' => 15,
            'min_stock' => 0,
            'active' => false,
        ]);
    }
}
