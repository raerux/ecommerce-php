<?php
/**
 * Página de Login de Clientes
 * 
 * Permite que clientes cadastrados façam login no sistema
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Se já estiver logado, redireciona para a home
if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Entrar';
$errors = [];

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    // Validações
    if (empty($email)) {
        $errors[] = 'O email é obrigatório';
    } elseif (!validarEmail($email)) {
        $errors[] = 'Email inválido';
    }
    
    if (empty($senha)) {
        $errors[] = 'A senha é obrigatória';
    }
    
    // Se não houver erros de validação, tenta autenticar
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            
            $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM clientes WHERE email = ?");
            $stmt->execute([$email]);
            $cliente = $stmt->fetch();
            
            if ($cliente && password_verify($senha, $cliente['senha'])) {
                // Login bem-sucedido
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                $_SESSION['cliente_email'] = $cliente['email'];
                
                setFlashMessage('Bem-vindo(a), ' . $cliente['nome'] . '!', 'success');
                
                // Redireciona para a página anterior ou home
                $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
                unset($_SESSION['redirect_after_login']);
                
                redirect($redirect);
            } else {
                $errors[] = 'Email ou senha incorretos';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erro ao realizar login. Tente novamente.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-header">
        <h1>🔐 Entrar</h1>
        <p>Acesse sua conta para continuar suas compras</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo escape($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="seu@email.com"
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
        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
