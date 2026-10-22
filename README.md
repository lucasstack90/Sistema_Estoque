# Sistema de Controle de Estoque – E-commerce de Eletrônicos

Sistema web para controlar o estoque de uma loja virtual de produtos eletrônicos.

**Tecnologias:** PHP 8.3+ com Laravel 13 (back-end), HTML, CSS, JavaScript e Bootstrap 5 (front-end), MySQL/MariaDB (banco de dados).

## Funcionalidades

- **Login** com dois perfis:
  - **Administrador**: acesso total.
  - **Operador**: consulta produtos e registra movimentações.
- **Dashboard**:
  - indicadores: produtos ativos, unidades, valor em estoque (custo e venda), estoque baixo e sem estoque;
  - gráfico de entradas x saídas dos últimos 30 dias;
  - valor em estoque por categoria;
  - mais vendidos;
  - produtos que precisam de reposição;
  - últimas movimentações.
- **Produtos**:
  - cadastro com SKU, marca, modelo, categoria, fornecedor, preços, estoque mínimo, localização no depósito e garantia;
  - busca, filtros (categoria, estoque baixo, sem estoque, ativos/inativos) e ordenação;
  - cálculo automático de margem;
  - histórico de movimentações por produto.
- **Movimentações**:
  - tipos: **entrada**, **saída** e **ajuste de inventário**, cada um com motivo, documento (NF/pedido), observações e usuário responsável;
  - o saldo só muda por movimentação (rastreabilidade total);
  - o sistema não deixa o estoque ficar negativo;
  - a entrada com custo unitário recalcula o **custo médio** do produto;
  - prévia do saldo final na tela antes de confirmar.
- **Categorias**, **Fornecedores** (com CNPJ) e **Usuários**.
- **Relatórios em CSV**: produtos e movimentações, respeitando os filtros da tela. Abrem direto no Excel.

## Como rodar

### Pré-requisitos

- PHP 8.3 ou superior, com estas extensões: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`
- [Composer](https://getcomposer.org/)
- MySQL 8 ou MariaDB 10.6+

No Windows, a forma mais fácil é instalar o **[Laragon](https://laragon.org/)** ou o **XAMPP** (com PHP 8.3+), que já trazem PHP e MySQL.

### Passo a passo

```bash
# 1. Instalar as dependências
composer install

# 2. Criar o arquivo de configuração
cp .env.example .env        # no Windows: copy .env.example .env
php artisan key:generate

# 3. Editar o .env com os dados do seu MySQL (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
#    e criar o banco "estoque" no MySQL

# 4. Criar as tabelas e os dados de demonstração
php artisan migrate --seed

# 5. Iniciar o servidor
php artisan serve
```

Acesse **http://localhost:8000**.

**Alternativa ao passo 4:** importar o script SQL pronto, que já traz a estrutura e os dados de demonstração:

```bash
mysql -u root -p < database/estoque.sql
```

### Usuários de demonstração

| Perfil        | E-mail               | Senha       |
|---------------|----------------------|-------------|
| Administrador | admin@estoque.com    | admin123    |
| Operador      | operador@estoque.com | operador123 |

> Troque essas senhas antes de usar o sistema em produção. Isso pode ser feito em **Usuários**.

## Testes automatizados

```bash
php artisan test
```

## Estrutura principal

| Caminho | Conteúdo |
|---------|----------|
| `app/Models` | Product, Category, Supplier, StockMovement, User |
| `app/Services/StockService.php` | regra de negócio das movimentações (transação + bloqueio de linha) |
| `app/Http/Controllers` | telas e relatórios |
| `database/migrations` | estrutura das tabelas |
| `database/seeders` | dados de demonstração |
| `database/estoque.sql` | script SQL completo |
| `resources/views` | telas (Blade + Bootstrap) |
| `public/css/app.css`, `public/js/app.js` | estilos e scripts próprios |

Bootstrap, Bootstrap Icons e Chart.js são carregados via CDN, então o navegador precisa de internet.
