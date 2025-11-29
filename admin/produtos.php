<?php
/**
 * CRUD de Produtos - Área Administrativa
 * 
 * Permite gerenciar os produtos do e-commerce
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Verifica se está logado como admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$baseUrl = '../';
$pageTitle = 'Admin - Produtos';

$pdo = getConnection();

$errors = [];
$success = '';
$editando = false;
$produtoEdit = null;

// Processa ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'criar' || $action === 'editar') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = floatval(str_replace(',', '.', $_POST['preco'] ?? 0));
        $estoque = (int) ($_POST['estoque'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? '');
        $imagem = trim($_POST['imagem'] ?? '');
        
        // Validações
        if (empty($nome)) {
            $errors[] = 'O nome é obrigatório';
        }
        
        if ($preco <= 0) {
            $errors[] = 'O preço deve ser maior que zero';
        }
        
        if ($estoque < 0) {
            $errors[] = 'O estoque não pode ser negativo';
        }
        
        if (empty($errors)) {
            try {
                if ($action === 'criar') {
                    $stmt = $pdo->prepare("
                        INSERT INTO produtos (nome, descricao, preco, estoque, categoria, imagem)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$nome, $descricao, $preco, $estoque, $categoria, $imagem]);
                    $success = 'Produto criado com sucesso!';
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE produtos 
                        SET nome = ?, descricao = ?, preco = ?, estoque = ?, categoria = ?, imagem = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$nome, $descricao, $preco, $estoque, $categoria, $imagem, $id]);
                    $success = 'Produto atualizado com sucesso!';
                }
            } catch (PDOException $e) {
                error_log("Erro ao salvar produto: " . $e->getMessage());
                $errors[] = 'Erro ao salvar produto. Tente novamente.';
            }
        }
    } elseif ($action === 'deletar') {
        $id = (int) ($_POST['id'] ?? 0);
        
        if ($id > 0) {
            try {
                // Soft delete - marca como inativo
                $stmt = $pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Produto removido com sucesso!';
            } catch (PDOException $e) {
                $errors[] = 'Erro ao remover produto';
            }
        }
    }
}

// Verifica se está editando
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produtoEdit = $stmt->fetch();
    
    if ($produtoEdit) {
        $editando = true;
    }
}

// Busca todos os produtos
$produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY data_cadastro DESC")->fetchAll();

// Busca categorias existentes
$categorias = $pdo->query("SELECT DISTINCT categoria FROM produtos WHERE ativo = 1 AND categoria IS NOT NULL ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

include 'includes/admin_header.php';
?>

<div class="admin-actions">
    <h1>📦 Gerenciar Produtos</h1>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('formProduto').scrollIntoView({behavior: 'smooth'})">
        + Novo Produto
    </button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo escape($success); ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Formulário de Produto -->
<div class="card mb-2" id="formProduto">
    <div class="card-header">
        <h3><?php echo $editando ? '✏️ Editar Produto' : '➕ Novo Produto'; ?></h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="<?php echo $editando ? 'editar' : 'criar'; ?>">
            <?php if ($editando): ?>
                <input type="hidden" name="id" value="<?php echo $produtoEdit['id']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label for="nome" class="form-label">Nome do Produto *</label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           class="form-control" 
                           value="<?php echo $editando ? escape($produtoEdit['nome']) : ''; ?>"
                           required>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label for="categoria" class="form-label">Categoria</label>
                    <input type="text" 
                           id="categoria" 
                           name="categoria" 
                           class="form-control" 
                           list="categorias"
                           value="<?php echo $editando ? escape($produtoEdit['categoria']) : ''; ?>">
                    <datalist id="categorias">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo escape($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>
            
            <div class="form-group">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" 
                          name="descricao" 
                          class="form-control" 
                          rows="3"><?php echo $editando ? escape($produtoEdit['descricao']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="preco" class="form-label">Preço (R$) *</label>
                    <input type="number" 
                           id="preco" 
                           name="preco" 
                           class="form-control" 
                           step="0.01"
                           min="0"
                           value="<?php echo $editando ? $produtoEdit['preco'] : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="estoque" class="form-label">Estoque *</label>
                    <input type="number" 
                           id="estoque" 
                           name="estoque" 
                           class="form-control" 
                           min="0"
                           value="<?php echo $editando ? $produtoEdit['estoque'] : '0'; ?>"
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="imagem" class="form-label">URL da Imagem</label>
                <input type="url" 
                       id="imagem" 
                       name="imagem" 
                       class="form-control" 
                       placeholder="https://exemplo.com/imagem.jpg"
                       value="<?php echo $editando ? escape($produtoEdit['imagem']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">
                    <?php echo $editando ? 'Salvar Alterações' : 'Cadastrar Produto'; ?>
                </button>
                <?php if ($editando): ?>
                    <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Produtos -->
<div class="card">
    <div class="card-header">
        <h3>📋 Produtos Cadastrados (<?php echo count($produtos); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($produtos)): ?>
            <p class="text-muted text-center">Nenhum produto cadastrado</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo $produto['id']; ?></td>
                                <td>
                                    <img src="<?php echo escape($produto['imagem']); ?>" 
                                         alt="<?php echo escape($produto['nome']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                         onerror="this.src='https://via.placeholder.com/50x50?text=Img'">
                                </td>
                                <td><?php echo escape($produto['nome']); ?></td>
                                <td><?php echo escape($produto['categoria']); ?></td>
                                <td><?php echo formatMoney($produto['preco']); ?></td>
                                <td>
                                    <span class="<?php echo $produto['estoque'] < 5 ? ($produto['estoque'] == 0 ? 'text-danger' : 'text-warning') : ''; ?>">
                                        <?php echo $produto['estoque']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="produtos.php?editar=<?php echo $produto['id']; ?>" 
                                           class="btn btn-sm btn-outline">
                                            ✏️ Editar
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Tem certeza que deseja remover este produto?')">
                                            <input type="hidden" name="action" value="deletar">
                                            <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                🗑️ Remover
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
