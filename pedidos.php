<?php
/**
 * Página de Histórico de Pedidos
 * 
 * Exibe todos os pedidos do cliente logado
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Verifica se o usuário está logado
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'pedidos.php';
    setFlashMessage('Faça login para ver seus pedidos.', 'warning');
    redirect('login.php');
}

$pageTitle = 'Meus Pedidos';

$pdo = getConnection();

// Busca os pedidos do cliente
$stmt = $pdo->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM itens_compra ic WHERE ic.compra_id = c.id) as total_itens
    FROM compras c 
    WHERE c.cliente_id = ? 
    ORDER BY c.data_compra DESC
");
$stmt->execute([$_SESSION['cliente_id']]);
$pedidos = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-title">
    <h1>📋 Meus Pedidos</h1>
    <p>Acompanhe o status de todas as suas compras</p>
</div>

<?php if (empty($pedidos)): ?>
    <div class="cart-empty">
        <div class="cart-empty-icon">📦</div>
        <h2>Você ainda não fez nenhuma compra</h2>
        <p>Explore nossos produtos e faça sua primeira compra!</p>
        <a href="index.php" class="btn btn-primary btn-lg">Ver Produtos</a>
    </div>
<?php else: ?>
    <div class="orders-list">
        <?php foreach ($pedidos as $pedido): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="order-number">Pedido #<?php echo $pedido['id']; ?></span>
                        <span class="text-muted" style="margin-left: 1rem;">
                            <?php echo date('d/m/Y H:i', strtotime($pedido['data_compra'])); ?>
                        </span>
                    </div>
                    <span class="order-status <?php echo strtolower($pedido['status']); ?>">
                        <?php echo escape($pedido['status']); ?>
                    </span>
                </div>
                <div class="order-body">
                    <div class="order-info">
                        <div class="order-info-item">
                            <label>Itens</label>
                            <span><?php echo $pedido['total_itens']; ?> produto(s)</span>
                        </div>
                        <div class="order-info-item">
                            <label>Produtos</label>
                            <span><?php echo formatMoney($pedido['valor_produtos']); ?></span>
                        </div>
                        <div class="order-info-item">
                            <label>Frete</label>
                            <span><?php echo formatMoney($pedido['valor_frete']); ?></span>
                        </div>
                        <div class="order-info-item">
                            <label>Total</label>
                            <span style="color: var(--success-color); font-size: 1.125rem;">
                                <?php echo formatMoney($pedido['valor_total']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php
                    // Busca os itens do pedido
                    $stmtItens = $pdo->prepare("
                        SELECT ic.*, p.nome, p.imagem
                        FROM itens_compra ic
                        JOIN produtos p ON p.id = ic.produto_id
                        WHERE ic.compra_id = ?
                    ");
                    $stmtItens->execute([$pedido['id']]);
                    $itens = $stmtItens->fetchAll();
                    ?>
                    
                    <details style="margin-top: 1rem;">
                        <summary style="cursor: pointer; font-weight: 500; color: var(--primary-color);">
                            Ver detalhes do pedido
                        </summary>
                        <div style="margin-top: 1rem;">
                            <p><strong>Endereço de entrega:</strong> CEP <?php echo escape($pedido['cep_destino']); ?></p>
                            
                            <div class="table-responsive" style="margin-top: 1rem;">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Qtd</th>
                                            <th>Preço Unit.</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($itens as $item): ?>
                                            <tr>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                        <img src="<?php echo escape($item['imagem']); ?>" 
                                                             alt="<?php echo escape($item['nome']); ?>"
                                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"
                                                             onerror="this.src='https://via.placeholder.com/40x40?text=Img'">
                                                        <?php echo escape($item['nome']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo $item['quantidade']; ?></td>
                                                <td><?php echo formatMoney($item['preco_unitario']); ?></td>
                                                <td><?php echo formatMoney($item['subtotal']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
