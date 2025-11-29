<?php
/**
 * Página de Checkout - Finalização de Compra
 * 
 * Permite que o cliente insira o endereço de entrega e finalize a compra
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Verifica se o usuário está logado
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    setFlashMessage('Faça login para continuar com a compra.', 'warning');
    redirect('login.php');
}

// Verifica se o carrinho está vazio
if (empty($_SESSION['carrinho'])) {
    setFlashMessage('Seu carrinho está vazio.', 'warning');
    redirect('index.php');
}

$pageTitle = 'Checkout';
$includeCarrinhoJS = true;
$errors = [];

$pdo = getConnection();

// Obtém dados do cliente
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$_SESSION['cliente_id']]);
$cliente = $stmt->fetch();

// Processa a finalização da compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida token CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Token de segurança inválido. Tente novamente.';
    } else {
        $cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $valorFrete = floatval($_POST['valor_frete'] ?? 0);
        
        // Validações
        if (empty($cep) || strlen($cep) !== 8) {
            $errors[] = 'CEP inválido';
        }
        
        if (empty($endereco)) {
            $errors[] = 'O endereço é obrigatório';
        }
        
        if (empty($numero)) {
            $errors[] = 'O número é obrigatório';
        }
        
        if (empty($bairro)) {
            $errors[] = 'O bairro é obrigatório';
        }
        
        if (empty($cidade)) {
            $errors[] = 'A cidade é obrigatória';
        }
        
        if (empty($estado) || strlen($estado) !== 2) {
            $errors[] = 'O estado é obrigatório';
        }
        
        // Valida e recalcula o frete
        $freteCalculado = calcularFrete($cep);
        if ($valorFrete <= 0) {
            $valorFrete = $freteCalculado;
        }
        
        // Verifica estoque dos produtos
        $itensCarrinho = $_SESSION['carrinho'];
        foreach ($itensCarrinho as $produtoId => $item) {
            $stmt = $pdo->prepare("SELECT nome, estoque FROM produtos WHERE id = ? AND ativo = 1");
            $stmt->execute([$produtoId]);
            $produto = $stmt->fetch();
            
            if (!$produto) {
                $errors[] = "Produto '{$item['nome']}' não está mais disponível.";
            } elseif ($produto['estoque'] < $item['quantidade']) {
                $errors[] = "Produto '{$produto['nome']}' possui apenas {$produto['estoque']} unidade(s) em estoque.";
            }
        }
        
        // Se não houver erros, processa a compra
        if (empty($errors)) {
            try {
                /** @var PDO $pdo */
                $pdo->beginTransaction();
                
                $valorProdutos = getCartTotal();
                $valorTotal = $valorProdutos + $valorFrete;
                $cepDestino = substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
                
                // Insere a compra
                $stmt = $pdo->prepare("
                    INSERT INTO compras (cliente_id, valor_produtos, valor_frete, valor_total, cep_destino, status)
                    VALUES (?, ?, ?, ?, ?, 'Pendente')
                ");
                $stmt->execute([
                    $_SESSION['cliente_id'],
                    $valorProdutos,
                    $valorFrete,
                    $valorTotal,
                    $cepDestino
                ]);
                
                $compraId = $pdo->lastInsertId();
                
                // Insere os itens da compra e atualiza estoque
                foreach ($itensCarrinho as $produtoId => $item) {
                    $subtotal = $item['preco'] * $item['quantidade'];
                    
                    // Insere item
                    $stmt = $pdo->prepare("
                        INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario, subtotal)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $compraId,
                        $produtoId,
                        $item['quantidade'],
                        $item['preco'],
                        $subtotal
                    ]);
                    
                    // Atualiza estoque
                    $stmt = $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");
                    $stmt->execute([$item['quantidade'], $produtoId]);
                }
                
                // Atualiza endereço do cliente se não tiver
                if (empty($cliente['cep'])) {
                    $stmt = $pdo->prepare("
                        UPDATE clientes 
                        SET cep = ?, endereco = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $cepDestino,
                        $endereco,
                        $numero,
                        $complemento,
                        $bairro,
                        $cidade,
                        $estado,
                        $_SESSION['cliente_id']
                    ]);
                }
                
                $pdo->commit();
                
                // Limpa o carrinho
                unset($_SESSION['carrinho']);
                
                setFlashMessage("Compra realizada com sucesso! Pedido #$compraId", 'success');
                redirect('pedidos.php');
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Erro ao processar compra: " . $e->getMessage());
                $errors[] = 'Erro ao processar a compra. Tente novamente.';
            }
        }
    }
}

// Dados do carrinho
$itensCarrinho = $_SESSION['carrinho'];
$totalProdutos = getCartTotal();
$frete = 0;

// Se o cliente já tem CEP, calcula o frete
if (!empty($cliente['cep'])) {
    $frete = calcularFrete($cliente['cep']);
}

$csrfToken = generateCSRFToken();

include 'includes/header.php';
?>

<div class="page-title">
    <h1>📦 Finalizar Compra</h1>
    <p>Confirme seu endereço e finalize seu pedido</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="checkout-container">
    <div class="checkout-form">
        <form method="POST" action="" id="checkoutForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="valor_frete" id="valor_frete" value="<?php echo $frete; ?>">
            
            <!-- Endereço de Entrega -->
            <div class="card mb-2">
                <div class="card-header">
                    <h3>📍 Endereço de Entrega</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="cep" class="form-label">CEP *</label>
                        <div class="cep-search">
                            <input type="text" 
                                   id="cep" 
                                   name="cep" 
                                   class="form-control" 
                                   placeholder="00000-000"
                                   maxlength="9"
                                   value="<?php echo isset($_POST['cep']) ? escape($_POST['cep']) : (isset($cliente['cep']) ? escape($cliente['cep']) : ''); ?>"
                                   required>
                            <button type="button" class="btn btn-secondary btn-buscar-cep">Buscar</button>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 3;">
                            <label for="endereco" class="form-label">Endereço *</label>
                            <input type="text" 
                                   id="endereco" 
                                   name="endereco" 
                                   class="form-control" 
                                   placeholder="Rua, Avenida..."
                                   value="<?php echo isset($_POST['endereco']) ? escape($_POST['endereco']) : (isset($cliente['endereco']) ? escape($cliente['endereco']) : ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="numero" class="form-label">Número *</label>
                            <input type="text" 
                                   id="numero" 
                                   name="numero" 
                                   class="form-control" 
                                   placeholder="Nº"
                                   value="<?php echo isset($_POST['numero']) ? escape($_POST['numero']) : (isset($cliente['numero']) ? escape($cliente['numero']) : ''); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" 
                                   id="complemento" 
                                   name="complemento" 
                                   class="form-control" 
                                   placeholder="Apto, Bloco..."
                                   value="<?php echo isset($_POST['complemento']) ? escape($_POST['complemento']) : (isset($cliente['complemento']) ? escape($cliente['complemento']) : ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="bairro" class="form-label">Bairro *</label>
                            <input type="text" 
                                   id="bairro" 
                                   name="bairro" 
                                   class="form-control" 
                                   placeholder="Bairro"
                                   value="<?php echo isset($_POST['bairro']) ? escape($_POST['bairro']) : (isset($cliente['bairro']) ? escape($cliente['bairro']) : ''); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 3;">
                            <label for="cidade" class="form-label">Cidade *</label>
                            <input type="text" 
                                   id="cidade" 
                                   name="cidade" 
                                   class="form-control" 
                                   placeholder="Cidade"
                                   value="<?php echo isset($_POST['cidade']) ? escape($_POST['cidade']) : (isset($cliente['cidade']) ? escape($cliente['cidade']) : ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="estado" class="form-label">Estado *</label>
                            <input type="text" 
                                   id="estado" 
                                   name="estado" 
                                   class="form-control" 
                                   placeholder="UF"
                                   maxlength="2"
                                   value="<?php echo isset($_POST['estado']) ? escape($_POST['estado']) : (isset($cliente['estado']) ? escape($cliente['estado']) : ''); ?>"
                                   required>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Itens do Pedido -->
            <div class="card">
                <div class="card-header">
                    <h3>📋 Itens do Pedido</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Preço</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itensCarrinho as $item): ?>
                                    <tr>
                                        <td><?php echo escape($item['nome']); ?></td>
                                        <td><?php echo $item['quantidade']; ?></td>
                                        <td><?php echo formatMoney($item['preco']); ?></td>
                                        <td><?php echo formatMoney($item['preco'] * $item['quantidade']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Resumo do Pedido -->
    <div class="cart-summary">
        <div class="card">
            <div class="card-header">
                <h3>💰 Resumo do Pedido</h3>
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
                    <span class="valor-frete">
                        <?php echo $frete > 0 ? formatMoney($frete) : 'Calcular pelo CEP'; ?>
                    </span>
                </div>
                <div class="cart-summary-row total">
                    <span>Total</span>
                    <span class="total-geral">
                        <?php echo formatMoney($totalProdutos + $frete); ?>
                    </span>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" form="checkoutForm" class="btn btn-success btn-block btn-lg">
                    ✅ Confirmar Pedido
                </button>
                <a href="carrinho.php" class="btn btn-outline btn-block mt-1">
                    ← Voltar ao Carrinho
                </a>
            </div>
        </div>
        
        <div class="card mt-1" style="background: #f0f9ff; border: 1px solid #bae6fd;">
            <div class="card-body">
                <p style="margin: 0; font-size: 0.875rem; color: #0369a1;">
                    🔒 <strong>Compra Segura</strong><br>
                    Seus dados estão protegidos e sua compra é 100% segura.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
