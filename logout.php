<?php
/**
 * Página de Logout
 * 
 * Destrói a sessão do usuário e redireciona para a home
 */

require_once 'includes/functions.php';

// Limpa todas as variáveis da sessão
$_SESSION = [];

// Destrói o cookie da sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Regenera o ID da sessão e destrói a antiga
session_regenerate_id(true);

// Define mensagem de sucesso
$_SESSION['flash_message'] = [
    'message' => 'Você saiu com sucesso. Até logo!',
    'type' => 'success'
];

// Redireciona para a home
header("Location: index.php");
exit;
