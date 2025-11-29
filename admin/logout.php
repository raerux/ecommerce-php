<?php
/**
 * Logout Administrativo
 * 
 * Destrói a sessão de admin e redireciona para o login
 */

require_once '../includes/functions.php';

// Remove variáveis de admin da sessão
unset($_SESSION['admin_logado']);
unset($_SESSION['admin_email']);

setFlashMessage('Você saiu do painel administrativo.', 'success');

// Redireciona para o login admin
redirect('login.php');
