        </div>
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>🛒 E-Commerce PHP</h4>
                    <p>Sua loja online completa e segura.</p>
                </div>
                <div class="footer-section">
                    <h4>Links Úteis</h4>
                    <ul>
                        <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>index.php">Produtos</a></li>
                        <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>carrinho.php">Carrinho</a></li>
                        <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>pedidos.php">Meus Pedidos</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contato</h4>
                    <p>📧 contato@ecommerce.com</p>
                    <p>📱 (11) 99999-9999</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> E-Commerce PHP. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>js/main.js"></script>
    <?php if (isset($includeCarrinhoJS) && $includeCarrinhoJS): ?>
        <script src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>js/carrinho.js"></script>
    <?php endif; ?>
</body>
</html>
