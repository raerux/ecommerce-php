<?php
/**
 * Cabeçalho Comum - E-Commerce
 * 
 * Este arquivo é incluído em todas as páginas públicas.
 */

require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' : ''; ?>E-Commerce PHP</title>
    <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>index.php" class="logo">
                    🛒 E-Commerce
                </a>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>index.php">Produtos</a></li>
                        <li>
                            <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>carrinho.php" class="cart-link">
                                🛍️ Carrinho
                                <?php if (getCartCount() > 0): ?>
                                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>pedidos.php">Meus Pedidos</a></li>
                            <li>
                                <span class="user-greeting">Olá, <?php echo escape($_SESSION['cliente_nome'] ?? 'Cliente'); ?></span>
                            </li>
                            <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>logout.php" class="btn btn-outline btn-sm">Sair</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>login.php" class="btn btn-outline btn-sm">Entrar</a></li>
                            <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>cadastro.php" class="btn btn-primary btn-sm">Cadastrar</a></li>
                        <?php endif; ?>
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
