<?php
/**
 * Página de Cadastro de Clientes
 * 
 * Permite que novos usuários se cadastrem no sistema
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Se já estiver logado, redireciona para a home
if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Cadastrar';
$errors = [];
$success = false;

// Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmaSenha = $_POST['confirma_senha'] ?? '';
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    
    // Validações
    if (empty($nome)) {
        $errors[] = 'O nome é obrigatório';
    } elseif (strlen($nome) < 3) {
        $errors[] = 'O nome deve ter pelo menos 3 caracteres';
    }
    
    if (empty($email)) {
        $errors[] = 'O email é obrigatório';
    } elseif (!validarEmail($email)) {
        $errors[] = 'Email inválido';
    }
    
    if (empty($senha)) {
        $errors[] = 'A senha é obrigatória';
    } elseif (strlen($senha) < 6) {
        $errors[] = 'A senha deve ter pelo menos 6 caracteres';
    }
    
    if ($senha !== $confirmaSenha) {
        $errors[] = 'As senhas não conferem';
    }
    
    if (!empty($cpf) && !validarCPF($cpf)) {
        $errors[] = 'CPF inválido';
    }
    
    // Se não houver erros de validação
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            
            // Verifica se o email já está cadastrado
            $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Este email já está cadastrado';
            } else {
                // Verifica se o CPF já está cadastrado (se informado)
                if (!empty($cpf)) {
                    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE cpf = ?");
                    $stmt->execute([$cpf]);
                    
                    if ($stmt->fetch()) {
                        $errors[] = 'Este CPF já está cadastrado';
                    }
                }
            }
            
            if (empty($errors)) {
                // Cria hash da senha
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Insere o cliente
                $stmt = $pdo->prepare("
                    INSERT INTO clientes (nome, email, senha, cpf, telefone) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $nome,
                    $email,
                    $senhaHash,
                    $cpf ?: null,
                    $telefone ?: null
                ]);
                
                $success = true;
                setFlashMessage('Cadastro realizado com sucesso! Faça login para continuar.', 'success');
                redirect('login.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Erro ao realizar cadastro. Tente novamente.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-header">
        <h1>📝 Criar Conta</h1>
        <p>Preencha os dados abaixo para se cadastrar</p>
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
                    <label for="nome" class="form-label">Nome Completo *</label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           class="form-control" 
                           placeholder="Seu nome completo"
                           value="<?php echo isset($_POST['nome']) ? escape($_POST['nome']) : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="seu@email.com"
                           value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>"
                           required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" 
                               id="cpf" 
                               name="cpf" 
                               class="form-control" 
                               placeholder="000.000.000-00"
                               value="<?php echo isset($_POST['cpf']) ? escape($_POST['cpf']) : ''; ?>"
                               maxlength="14"
                               oninput="maskCPF(this)">
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" 
                               id="telefone" 
                               name="telefone" 
                               class="form-control" 
                               placeholder="(00) 00000-0000"
                               value="<?php echo isset($_POST['telefone']) ? escape($_POST['telefone']) : ''; ?>"
                               maxlength="15"
                               oninput="maskPhone(this)">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="senha" class="form-label">Senha *</label>
                        <input type="password" 
                               id="senha" 
                               name="senha" 
                               class="form-control" 
                               placeholder="Mínimo 6 caracteres"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirma_senha" class="form-label">Confirmar Senha *</label>
                        <input type="password" 
                               id="confirma_senha" 
                               name="confirma_senha" 
                               class="form-control" 
                               placeholder="Repita a senha"
                               required>
                    </div>
                </div>
                
                <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">
                    * Campos obrigatórios
                </p>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Cadastrar
                </button>
            </form>
        </div>
    </div>
    
    <div class="auth-footer">
        <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
