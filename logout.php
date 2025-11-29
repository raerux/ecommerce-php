<?php
/**
 * Página de Logout
 * 
 * Destrói a sessão do usuário e redireciona para a home
 */

require_once 'includes/functions.php';

// Destrói a sessão
session_destroy();

// Inicia uma nova sessão para a mensagem flash
session_start();

setFlashMessage('Você saiu com sucesso. Até logo!', 'success');

// Redireciona para a home
redirect('index.php');
