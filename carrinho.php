<?php
/**
 * Página do Carrinho de Compras
 * 
 * Exibe os itens do carrinho e permite manipulação
 * Também processa requisições AJAX para adicionar/atualizar/remover itens
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Processa requisições AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $produtoId = isset($_POST['produto_id']) ? (int) $_POST['produto_id'] : 0;
    $quantidade = isset($_POST['quantidade']) ? (int) $_POST['quantidade'] : 1;
    
    // Inicializa o carrinho se não existir
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    
    $pdo = getConnection();
    
    switch ($action) {
        case 'add':
            // Busca dados do produto
            $stmt = $pdo->prepare("SELECT id, nome, preco, estoque, imagem FROM produtos WHERE id = ? AND ativo = 1");
            $stmt->execute([$produtoId]);
            $produto = $stmt->fetch();
            
            if (!$produto) {
                echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
                exit;
            }
            
            // Verifica estoque
            $qtdNoCarrinho = isset($_SESSION['carrinho'][$produtoId]) ? $_SESSION['carrinho'][$produtoId]['quantidade'] : 0;
            
            if ($qtdNoCarrinho + $quantidade > $produto['estoque']) {
                echo json_encode(['success' => false, 'message' => 'Quantidade indisponível em estoque']);
                exit;
            }
            
            // Adiciona ou atualiza item no carrinho
            if (isset($_SESSION['carrinho'][$produtoId])) {
                $_SESSION['carrinho'][$produtoId]['quantidade'] += $quantidade;
            } else {
                $_SESSION['carrinho'][$produtoId] = [
                    'id' => $produto['id'],
                    'nome' => $produto['nome'],
                    'preco' => $produto['preco'],
                    'imagem' => $produto['imagem'],
                    'quantidade' => $quantidade
                ];
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho',
                'cartCount' => getCartCount(),
                'cartTotal' => formatMoney(getCartTotal())
            ]);
            break;
            
        case 'update':
            if (!isset($_SESSION['carrinho'][$produtoId])) {
                echo json_encode(['success' => false, 'message' => 'Produto não encontrado no carrinho']);
                exit;
            }
            
            // Verifica estoque
            $stmt = $pdo->prepare("SELECT estoque FROM produtos WHERE id = ? AND ativo = 1");
            $stmt->execute([$produtoId]);
            $produto = $stmt->fetch();
            
            if (!$produto) {
                unset($_SESSION['carrinho'][$produtoId]);
                echo json_encode(['success' => false, 'message' => 'Produto não disponível']);
                exit;
            }
            
            if ($quantidade > $produto['estoque']) {
                echo json_encode(['success' => false, 'message' => 'Quantidade indisponível em estoque']);
                exit;
            }
            
            if ($quantidade < 1) {
                unset($_SESSION['carrinho'][$produtoId]);
            } else {
                $_SESSION['carrinho'][$produtoId]['quantidade'] = $quantidade;
            }
            
            $itemSubtotal = $quantidade * $_SESSION['carrinho'][$produtoId]['preco'];
            
            echo json_encode([
                'success' => true,
                'cartCount' => getCartCount(),
                'totalProdutos' => formatMoney(getCartTotal()),
                'totalGeral' => formatMoney(getCartTotal()),
                'itemSubtotal' => formatMoney($itemSubtotal)
            ]);
            break;
            
        case 'remove':
            if (isset($_SESSION['carrinho'][$produtoId])) {
                unset($_SESSION['carrinho'][$produtoId]);
            }
            
            echo json_encode([
                'success' => true,
                'cartCount' => getCartCount(),
                'totalProdutos' => formatMoney(getCartTotal()),
                'totalGeral' => formatMoney(getCartTotal())
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
    exit;
}

$pageTitle = 'Carrinho';
$includeCarrinhoJS = true;

// Obtém os itens do carrinho
$itensCarrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : [];
$totalProdutos = getCartTotal();

include 'includes/header.php';
?>

<div class="page-title">
    <h1>🛒 Meu Carrinho</h1>
    <p>Revise seus itens antes de finalizar a compra</p>
</div>

<?php if (empty($itensCarrinho)): ?>
    <div class="cart-empty">
        <div class="cart-empty-icon">🛒</div>
        <h2>Seu carrinho está vazio</h2>
        <p>Adicione produtos ao seu carrinho para continuar comprando.</p>
        <a href="index.php" class="btn btn-primary btn-lg">Ver Produtos</a>
    </div>
<?php else: ?>
    <div class="cart-container">
        <div class="cart-items-section">
            <div class="card">
                <div class="card-header">
                    <h3>Itens do Carrinho (<?php echo getCartCount(); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="cart-items">
                        <?php foreach ($itensCarrinho as $produtoId => $item): ?>
                            <div class="cart-item" data-produto-id="<?php echo $produtoId; ?>">
                                <img src="<?php echo escape($item['imagem']); ?>" 
                                     alt="<?php echo escape($item['nome']); ?>" 
                                     class="cart-item-image"
                                     onerror="this.src='https://via.placeholder.com/80x80?text=Sem+Imagem'">
                                
                                <div class="cart-item-details">
                                    <h3><?php echo escape($item['nome']); ?></h3>
                                    <p class="cart-item-price"><?php echo formatMoney($item['preco']); ?> cada</p>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <div class="quantity-control">
                                        <button type="button" data-action="minus">-</button>
                                        <input type="number" 
                                               class="cart-quantity" 
                                               value="<?php echo $item['quantidade']; ?>" 
                                               min="1" 
                                               max="99"
                                               data-produto-id="<?php echo $produtoId; ?>">
                                        <button type="button" data-action="plus">+</button>
                                    </div>
                                    
                                    <span class="cart-item-subtotal" data-produto-id="<?php echo $produtoId; ?>">
                                        <?php echo formatMoney($item['preco'] * $item['quantidade']); ?>
                                    </span>
                                    
                                    <button type="button" 
                                            class="cart-item-remove btn-remove-cart" 
                                            data-produto-id="<?php echo $produtoId; ?>"
                                            title="Remover item">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cart-summary">
            <div class="card">
                <div class="card-header">
                    <h3>Resumo do Pedido</h3>
                </div>
                <div class="card-body">
                    <div class="cart-summary-row">
                        <span>Produtos</span>
                        <span class="total-produtos" data-valor="<?php echo $totalProdutos; ?>">
                            <?php echo formatMoney($totalProdutos); ?>
                        </span>
                    </div>
                    <div class="cart-summary-row">
                        <span>Frete</span>
                        <span class="text-muted">Calculado no checkout</span>
                    </div>
                    <div class="cart-summary-row total">
                        <span>Total</span>
                        <span class="total-geral"><?php echo formatMoney($totalProdutos); ?></span>
                    </div>
                </div>
                <div class="card-footer">
                    <?php if (isLoggedIn()): ?>
                        <a href="checkout.php" class="btn btn-success btn-block btn-lg">
                            Finalizar Compra
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-success btn-block btn-lg" 
                           onclick="sessionStorage.setItem('redirect', 'checkout.php')">
                            Fazer Login para Continuar
                        </a>
                        <p class="text-center text-muted mt-1" style="font-size: 0.875rem;">
                            Não tem conta? <a href="cadastro.php">Cadastre-se</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 1rem;">
                <a href="index.php" class="btn btn-outline btn-block">
                    ← Continuar Comprando
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
