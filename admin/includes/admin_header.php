<?php
/**
 * Cabeçalho da Área Administrativa
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) : 'Admin'; ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>css/style.css">
</head>
<body>
    <header class="main-header admin-header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    🔐 Painel Admin
                </a>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">📊 Dashboard</a></li>
                        <li><a href="produtos.php">📦 Produtos</a></li>
                        <li><a href="compras.php">🛍️ Compras</a></li>
                        <li><a href="<?php echo $baseUrl; ?>index.php" target="_blank">🏪 Ver Loja</a></li>
                        <li><a href="<?php echo $baseUrl; ?>admin/logout.php" class="btn btn-outline btn-sm">Sair</a></li>
                    </ul>
                </nav>
                
                <button class="mobile-menu-toggle" aria-label="Menu">
                    ☰
                </button>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <?php
            // Exibe mensagem flash se existir
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="alert alert-<?php echo escape($flash['type']); ?>">
                    <?php echo escape($flash['message']); ?>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>
