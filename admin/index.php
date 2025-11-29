<?php
/**
 * Dashboard Administrativo
 * 
 * Exibe estatísticas gerais do sistema
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Verifica se está logado como admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$baseUrl = '../';
$pageTitle = 'Admin - Dashboard';

$pdo = getConnection();

// Estatísticas
$totalProdutos = $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn();
$totalClientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalCompras = $pdo->query("SELECT COUNT(*) FROM compras")->fetchColumn();
$totalVendas = $pdo->query("SELECT COALESCE(SUM(valor_total), 0) FROM compras")->fetchColumn();

// Últimas compras
$ultimasCompras = $pdo->query("
    SELECT c.*, cl.nome as cliente_nome
    FROM compras c
    JOIN clientes cl ON cl.id = c.cliente_id
    ORDER BY c.data_compra DESC
    LIMIT 5
")->fetchAll();

// Produtos com baixo estoque
$produtosBaixoEstoque = $pdo->query("
    SELECT * FROM produtos 
    WHERE ativo = 1 AND estoque <= 5 
    ORDER BY estoque ASC 
    LIMIT 5
")->fetchAll();

include 'includes/admin_header.php';
?>

<div class="page-title">
    <h1>📊 Dashboard</h1>
    <p>Visão geral do seu e-commerce</p>
</div>

<!-- Cards de Estatísticas -->
<div class="admin-dashboard">
    <div class="stat-card">
        <div class="stat-card-icon">📦</div>
        <div class="stat-card-value"><?php echo $totalProdutos; ?></div>
        <div class="stat-card-label">Produtos Ativos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-value"><?php echo $totalClientes; ?></div>
        <div class="stat-card-label">Clientes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-icon">🛍️</div>
        <div class="stat-card-value"><?php echo $totalCompras; ?></div>
        <div class="stat-card-label">Compras</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-icon">💰</div>
        <div class="stat-card-value"><?php echo formatMoney($totalVendas); ?></div>
        <div class="stat-card-label">Total em Vendas</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <!-- Últimas Compras -->
    <div class="card">
        <div class="card-header">
            <h3>🛍️ Últimas Compras</h3>
        </div>
        <div class="card-body">
            <?php if (empty($ultimasCompras)): ?>
                <p class="text-muted text-center">Nenhuma compra registrada</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimasCompras as $compra): ?>
                                <tr>
                                    <td><?php echo $compra['id']; ?></td>
                                    <td><?php echo escape($compra['cliente_nome']); ?></td>
                                    <td><?php echo formatMoney($compra['valor_total']); ?></td>
                                    <td>
                                        <span class="order-status <?php echo strtolower($compra['status']); ?>">
                                            <?php echo escape($compra['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-1">
                    <a href="compras.php" class="btn btn-outline btn-sm">Ver todas as compras</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Produtos com Baixo Estoque -->
    <div class="card">
        <div class="card-header">
            <h3>⚠️ Produtos com Baixo Estoque</h3>
        </div>
        <div class="card-body">
            <?php if (empty($produtosBaixoEstoque)): ?>
                <p class="text-success text-center">✅ Todos os produtos estão com estoque adequado</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Estoque</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtosBaixoEstoque as $produto): ?>
                                <tr>
                                    <td><?php echo escape($produto['nome']); ?></td>
                                    <td>
                                        <span class="<?php echo $produto['estoque'] == 0 ? 'text-danger' : 'text-warning'; ?>">
                                            <?php echo $produto['estoque']; ?> unidade(s)
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-1">
                    <a href="produtos.php" class="btn btn-outline btn-sm">Gerenciar produtos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
