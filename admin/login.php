<?php
/**
 * Login Administrativo
 * 
 * Permite que administradores acessem a área administrativa
 */

require_once '../includes/functions.php';

// Se já estiver logado como admin, redireciona para o dashboard
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$baseUrl = '../';
$pageTitle = 'Admin - Login';
$error = '';

// Credenciais hardcoded conforme especificação
define('ADMIN_EMAIL', 'admin@admin.com');
define('ADMIN_PASSWORD', 'admin123');

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if ($email === ADMIN_EMAIL && $senha === ADMIN_PASSWORD) {
        $_SESSION['admin_logado'] = true;
        $_SESSION['admin_email'] = $email;
        
        redirect('index.php');
    } else {
        $error = 'Email ou senha incorretos';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="main-header admin-header">
        <div class="container">
            <div class="header-content">
                <a href="login.php" class="logo">
                    🔐 Painel Administrativo
                </a>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>🔒 Acesso Restrito</h1>
                    <p>Área exclusiva para administradores</p>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-error">
                                <?php echo escape($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       placeholder="admin@admin.com"
                                       value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" 
                                       id="senha" 
                                       name="senha" 
                                       class="form-control" 
                                       placeholder="Sua senha"
                                       required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block btn-lg">
                                Entrar
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="auth-footer">
                    <p><a href="../index.php">← Voltar para a loja</a></p>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../js/main.js"></script>
</body>
</html>
