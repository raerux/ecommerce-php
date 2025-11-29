<?php
/**
 * Funções Auxiliares do E-Commerce
 * 
 * Este arquivo contém funções utilitárias usadas em todo o sistema.
 */

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escapa HTML para prevenir XSS
 * 
 * @param string $string String a ser escapada
 * @return string String escapada
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica se o usuário está logado
 * 
 * @return bool True se estiver logado, false caso contrário
 */
function isLoggedIn() {
    return isset($_SESSION['cliente_id']) && !empty($_SESSION['cliente_id']);
}

/**
 * Verifica se o admin está logado
 * 
 * @return bool True se estiver logado, false caso contrário
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true;
}

/**
 * Redireciona para uma URL
 * 
 * @param string $url URL de destino
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Define uma mensagem flash na sessão
 * 
 * @param string $message Mensagem a ser exibida
 * @param string $type Tipo da mensagem (success, error, warning, info)
 * @return void
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Obtém e remove a mensagem flash da sessão
 * 
 * @return array|null Array com mensagem e tipo, ou null se não houver
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Formata valor para moeda brasileira
 * 
 * @param float $value Valor a ser formatado
 * @return string Valor formatado (R$ X.XXX,XX)
 */
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Calcula o frete baseado no CEP
 * Regra simples: R$ 10 + R$ 2 por cada dígito par no CEP
 * 
 * @param string $cep CEP de destino (apenas números)
 * @return float Valor do frete
 */
function calcularFrete($cep) {
    // Remove caracteres não numéricos
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    $freteBase = 10.00;
    $acrescimoPorDigitoPar = 2.00;
    
    $digitosPares = 0;
    for ($i = 0; $i < strlen($cep); $i++) {
        $digito = (int) $cep[$i];
        if ($digito % 2 === 0) {
            $digitosPares++;
        }
    }
    
    return $freteBase + ($digitosPares * $acrescimoPorDigitoPar);
}

/**
 * Valida formato de email
 * 
 * @param string $email Email a ser validado
 * @return bool True se válido, false caso contrário
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida formato de CPF
 * 
 * @param string $cpf CPF a ser validado
 * @return bool True se válido, false caso contrário
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    
    // Calcula o primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += (int) $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Calcula o segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += (int) $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica se os dígitos calculados são iguais aos informados
    return ($cpf[9] == $digito1 && $cpf[10] == $digito2);
}

/**
 * Formata CPF para exibição (XXX.XXX.XXX-XX)
 * 
 * @param string $cpf CPF apenas números
 * @return string CPF formatado
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/**
 * Formata CEP para exibição (XXXXX-XXX)
 * 
 * @param string $cep CEP apenas números
 * @return string CEP formatado
 */
function formatarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
}

/**
 * Obtém a quantidade total de itens no carrinho
 * 
 * @return int Quantidade total de itens
 */
function getCartCount() {
    if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $total += $item['quantidade'];
    }
    return $total;
}

/**
 * Obtém o valor total do carrinho
 * 
 * @return float Valor total
 */
function getCartTotal() {
    if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
    return $total;
}

/**
 * Gera um token CSRF
 * 
 * @return string Token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF
 * 
 * @param string $token Token enviado pelo formulário
 * @return bool True se válido, false caso contrário
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
