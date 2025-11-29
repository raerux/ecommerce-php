<?php
/**
 * Página Principal - Catálogo de Produtos
 * 
 * Exibe todos os produtos ativos com opção de filtro por categoria
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Produtos';
$includeCarrinhoJS = true;

// Obtém conexão com o banco
$pdo = getConnection();

// Filtro por categoria
$categoriaFiltro = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

// Busca as categorias disponíveis
$stmtCategorias = $pdo->query("SELECT DISTINCT categoria FROM produtos WHERE ativo = 1 AND categoria IS NOT NULL ORDER BY categoria");
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_COLUMN);

// Busca os produtos
if ($categoriaFiltro) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE ativo = 1 AND categoria = ? ORDER BY data_cadastro DESC");
    $stmt->execute([$categoriaFiltro]);
} else {
    $stmt = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY data_cadastro DESC");
}
$produtos = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-title">
    <h1>🛍️ Nossos Produtos</h1>
    <p>Encontre os melhores produtos com os melhores preços</p>
</div>

<!-- Filtros -->
<div class="filters">
    <div class="filter-group">
        <label for="categoria">Categoria:</label>
        <select id="categoria" onchange="filtrarPorCategoria(this.value)">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo escape($cat); ?>" <?php echo $categoriaFiltro === $cat ? 'selected' : ''; ?>>
                    <?php echo escape($cat); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <span><?php echo count($produtos); ?> produto(s) encontrado(s)</span>
    </div>
</div>

<!-- Grid de Produtos -->
<?php if (empty($produtos)): ?>
    <div class="cart-empty">
        <div class="cart-empty-icon">📦</div>
        <h2>Nenhum produto encontrado</h2>
        <p>Não encontramos produtos para a categoria selecionada.</p>
        <a href="index.php" class="btn btn-primary">Ver todos os produtos</a>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($produtos as $produto): ?>
            <div class="product-card">
                <img src="<?php echo escape($produto['imagem']); ?>" 
                     alt="<?php echo escape($produto['nome']); ?>" 
                     class="product-image"
                     onerror="this.src='https://via.placeholder.com/400x300?text=Sem+Imagem'">
                <div class="product-info">
                    <span class="product-category"><?php echo escape($produto['categoria']); ?></span>
                    <h3 class="product-name"><?php echo escape($produto['nome']); ?></h3>
                    <div class="product-price"><?php echo formatMoney($produto['preco']); ?></div>
                    <div class="product-stock <?php echo $produto['estoque'] < 5 ? ($produto['estoque'] == 0 ? 'out' : 'low') : ''; ?>">
                        <?php if ($produto['estoque'] > 0): ?>
                            <?php echo $produto['estoque']; ?> em estoque
                        <?php else: ?>
                            Esgotado
                        <?php endif; ?>
                    </div>
                    <?php if ($produto['estoque'] > 0): ?>
                        <button class="btn btn-primary btn-block btn-add-cart" 
                                data-produto-id="<?php echo $produto['id']; ?>">
                            🛒 Adicionar
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-block" disabled>
                            Indisponível
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function filtrarPorCategoria(categoria) {
    if (categoria) {
        window.location.href = 'index.php?categoria=' + encodeURIComponent(categoria);
    } else {
        window.location.href = 'index.php';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
