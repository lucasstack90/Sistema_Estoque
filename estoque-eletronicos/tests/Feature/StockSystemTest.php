<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockSystemTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function operador(): User
    {
        return User::factory()->create(['role' => 'operador']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Controle de Estoque');
    }

    public function test_user_can_login_and_inactive_user_cannot(): void
    {
        $user = User::factory()->create(['password' => 'segredo123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'segredo123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        auth()->logout();
        $inactive = User::factory()->create(['password' => 'segredo123', 'active' => false]);
        $this->post('/login', ['email' => $inactive->email, 'password' => 'segredo123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_all_main_pages_render(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();
        $this->actingAs($admin);

        foreach (['/', '/produtos', '/produtos/create', "/produtos/{$product->id}", "/produtos/{$product->id}/edit",
            '/movimentacoes', '/movimentacoes/nova', '/categorias', '/categorias/create', '/fornecedores',
            '/fornecedores/create', '/usuarios', '/usuarios/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_creates_product_with_initial_stock(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())->post('/produtos', [
            'sku' => 'smp-test-1',
            'name' => 'Smartphone Teste',
            'brand' => 'Marca',
            'category_id' => $category->id,
            'cost_price' => '1.000,50',
            'sale_price' => '1.499,90',
            'min_stock' => 3,
            'initial_stock' => 10,
            'active' => '1',
        ])->assertRedirect();

        $product = Product::where('sku', 'SMP-TEST-1')->firstOrFail();
        $this->assertSame(10, $product->stock);
        $this->assertSame('1000.50', $product->cost_price);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'entrada', 'quantity' => 10, 'reason' => 'Estoque inicial']);
    }

    public function test_update_does_not_change_stock(): void
    {
        $product = Product::factory()->withStock(7)->create();

        $this->actingAs($this->admin())->put("/produtos/{$product->id}", [
            'sku' => $product->sku, 'name' => 'Novo nome', 'brand' => $product->brand,
            'category_id' => $product->category_id, 'cost_price' => '10', 'sale_price' => '20',
            'min_stock' => 1, 'stock' => 999,
        ])->assertRedirect();

        $product->refresh();
        $this->assertSame('Novo nome', $product->name);
        $this->assertSame(7, $product->stock);
        $this->assertFalse($product->active);
    }

    public function test_entry_exit_and_adjustment_update_stock(): void
    {
        $product = Product::factory()->withStock(10)->create(['cost_price' => 100]);
        $user = $this->operador();
        $this->actingAs($user);

        $this->post('/movimentacoes', ['product_id' => $product->id, 'type' => 'entrada', 'reason' => 'Compra de fornecedor', 'quantity' => 10, 'unit_cost' => '200,00'])
            ->assertRedirect(route('movements.index'));
        $product->refresh();
        $this->assertSame(20, $product->stock);
        $this->assertSame('150.00', $product->cost_price);

        $this->post('/movimentacoes', ['product_id' => $product->id, 'type' => 'saida', 'reason' => 'Venda', 'quantity' => 5]);
        $this->assertSame(15, $product->refresh()->stock);

        $this->post('/movimentacoes', ['product_id' => $product->id, 'type' => 'ajuste', 'reason' => 'Inventário', 'quantity' => 12]);
        $this->assertSame(12, $product->refresh()->stock);

        $adjust = StockMovement::latest('id')->first();
        $this->assertSame(3, $adjust->quantity);
        $this->assertSame(-3, $adjust->signedQuantity());
        $this->assertSame($user->id, $adjust->user_id);
    }

    public function test_exit_greater_than_stock_is_rejected(): void
    {
        $product = Product::factory()->withStock(2)->create();

        $this->actingAs($this->operador())
            ->post('/movimentacoes', ['product_id' => $product->id, 'type' => 'saida', 'reason' => 'Venda', 'quantity' => 3])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->refresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_reason_must_match_type(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $this->actingAs($this->operador())
            ->post('/movimentacoes', ['product_id' => $product->id, 'type' => 'entrada', 'reason' => 'Venda', 'quantity' => 1])
            ->assertSessionHasErrors('reason');
    }

    public function test_operador_cannot_access_admin_areas(): void
    {
        $product = Product::factory()->create();
        $this->actingAs($this->operador());

        $this->get('/produtos')->assertOk();
        $this->get("/produtos/{$product->id}")->assertOk();
        $this->get('/produtos/create')->assertForbidden();
        $this->get("/produtos/{$product->id}/edit")->assertForbidden();
        $this->delete("/produtos/{$product->id}")->assertForbidden();
        $this->get('/categorias')->assertForbidden();
        $this->get('/fornecedores')->assertForbidden();
        $this->get('/usuarios')->assertForbidden();
    }

    public function test_product_with_movements_cannot_be_deleted(): void
    {
        $product = Product::factory()->withStock(0)->create();
        $admin = $this->admin();
        app(\App\Services\StockService::class)->register($product, 'entrada', 1, 'Compra de fornecedor', $admin);

        $this->actingAs($admin)->delete("/produtos/{$product->id}")->assertSessionHas('error');
        $this->assertModelExists($product);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())->delete("/categorias/{$product->category_id}")->assertSessionHas('error');
        $this->assertModelExists($product->category);
    }

    public function test_csv_exports(): void
    {
        $product = Product::factory()->withStock(4)->create(['name' => 'Fone Exportado']);
        $this->actingAs($this->operador());

        $csv = $this->get('/relatorios/produtos')->assertOk()->streamedContent();
        $this->assertStringContainsString('Fone Exportado', $csv);
        $this->assertStringContainsString('SKU;Produto', $csv);

        $this->get('/relatorios/movimentacoes')->assertOk();
    }

    public function test_product_filters(): void
    {
        Product::factory()->withStock(0)->create(['name' => 'Produto Zerado']);
        Product::factory()->withStock(2)->create(['name' => 'Produto Baixo', 'min_stock' => 5]);
        Product::factory()->withStock(50)->create(['name' => 'Produto Cheio', 'min_stock' => 5]);
        $this->actingAs($this->operador());

        $this->get('/produtos?status=baixo')->assertSee('Produto Baixo')->assertDontSee('Produto Zerado')->assertDontSee('Produto Cheio');
        $this->get('/produtos?status=sem_estoque')->assertSee('Produto Zerado')->assertDontSee('Produto Baixo');
        $this->get('/produtos?q=Cheio')->assertSee('Produto Cheio')->assertDontSee('Produto Baixo');
    }
}
