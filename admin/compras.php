<?php
/**
 * Lista de Compras - Área Administrativa
 * 
 * Exibe todas as compras realizadas no e-commerce
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Verifica se está logado como admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$baseUrl = '../';
$pageTitle = 'Admin - Compras';

$pdo = getConnection();

// Atualiza status se solicitado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'atualizar_status') {
    $compraId = (int) ($_POST['compra_id'] ?? 0);
    $novoStatus = trim($_POST['status'] ?? '');
    
    $statusValidos = ['Pendente', 'Processando', 'Enviado', 'Entregue', 'Cancelado'];
    
    if ($compraId > 0 && in_array($novoStatus, $statusValidos)) {
        $stmt = $pdo->prepare("UPDATE compras SET status = ? WHERE id = ?");
        $stmt->execute([$novoStatus, $compraId]);
        
        setFlashMessage('Status atualizado com sucesso!', 'success');
        redirect('compras.php');
    }
}

// Filtro por status
$statusFiltro = isset($_GET['status']) ? trim($_GET['status']) : '';

// Busca as compras
if ($statusFiltro) {
    $stmt = $pdo->prepare("
        SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
        FROM compras c
        JOIN clientes cl ON cl.id = c.cliente_id
        WHERE c.status = ?
        ORDER BY c.data_compra DESC
    ");
    $stmt->execute([$statusFiltro]);
} else {
    $stmt = $pdo->query("
        SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
        FROM compras c
        JOIN clientes cl ON cl.id = c.cliente_id
        ORDER BY c.data_compra DESC
    ");
}
$compras = $stmt->fetchAll();

// Conta por status
$contagens = $pdo->query("
    SELECT status, COUNT(*) as total 
    FROM compras 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

include 'includes/admin_header.php';
?>

<div class="page-title">
    <h1>🛍️ Gerenciar Compras</h1>
    <p>Visualize e gerencie todos os pedidos</p>
</div>

<?php
$flash = getFlashMessage();
if ($flash):
?>
    <div class="alert alert-<?php echo escape($flash['type']); ?>">
        <?php echo escape($flash['message']); ?>
    </div>
<?php endif; ?>

<!-- Filtros por Status -->
<div class="filters">
    <div class="filter-group">
        <label>Filtrar por status:</label>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="compras.php" 
               class="btn btn-sm <?php echo empty($statusFiltro) ? 'btn-primary' : 'btn-outline'; ?>">
                Todos (<?php echo array_sum($contagens); ?>)
            </a>
            <?php 
            $statusList = ['Pendente', 'Processando', 'Enviado', 'Entregue', 'Cancelado'];
            foreach ($statusList as $status):
                $count = $contagens[$status] ?? 0;
            ?>
                <a href="compras.php?status=<?php echo urlencode($status); ?>" 
                   class="btn btn-sm <?php echo $statusFiltro === $status ? 'btn-primary' : 'btn-outline'; ?>">
                    <?php echo $status; ?> (<?php echo $count; ?>)
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Lista de Compras -->
<div class="card">
    <div class="card-body">
        <?php if (empty($compras)): ?>
            <p class="text-muted text-center">Nenhuma compra encontrada</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>CEP</th>
                            <th>Produtos</th>
                            <th>Frete</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td><strong>#<?php echo $compra['id']; ?></strong></td>
                                <td>
                                    <?php echo escape($compra['cliente_nome']); ?>
                                    <br>
                                    <small class="text-muted"><?php echo escape($compra['cliente_email']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></td>
                                <td><?php echo escape($compra['cep_destino']); ?></td>
                                <td><?php echo formatMoney($compra['valor_produtos']); ?></td>
                                <td><?php echo formatMoney($compra['valor_frete']); ?></td>
                                <td><strong><?php echo formatMoney($compra['valor_total']); ?></strong></td>
                                <td>
                                    <span class="order-status <?php echo strtolower($compra['status']); ?>">
                                        <?php echo escape($compra['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline"
                                                onclick="toggleDetails(<?php echo $compra['id']; ?>)">
                                            👁️ Ver
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="atualizar_status">
                                            <input type="hidden" name="compra_id" value="<?php echo $compra['id']; ?>">
                                            <select name="status" 
                                                    class="form-control" 
                                                    style="display: inline-block; width: auto; padding: 0.25rem;"
                                                    onchange="this.form.submit()">
                                                <?php foreach ($statusList as $status): ?>
                                                    <option value="<?php echo $status; ?>" 
                                                            <?php echo $compra['status'] === $status ? 'selected' : ''; ?>>
                                                        <?php echo $status; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr id="details-<?php echo $compra['id']; ?>" style="display: none;">
                                <td colspan="9" style="background: var(--gray-50);">
                                    <div style="padding: 1rem;">
                                        <h4 style="margin-bottom: 1rem;">📦 Itens do Pedido #<?php echo $compra['id']; ?></h4>
                                        <?php
                                        $stmtItens = $pdo->prepare("
                                            SELECT ic.*, p.nome, p.imagem
                                            FROM itens_compra ic
                                            JOIN produtos p ON p.id = ic.produto_id
                                            WHERE ic.compra_id = ?
                                        ");
                                        $stmtItens->execute([$compra['id']]);
                                        $itens = $stmtItens->fetchAll();
                                        ?>
                                        <table class="table" style="margin: 0;">
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
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDetails(compraId) {
    const row = document.getElementById('details-' + compraId);
    if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>
