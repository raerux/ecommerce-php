<?php
/**
 * API de CEP - Proxy para ViaCEP
 * 
 * Este arquivo serve como proxy para a API ViaCEP,
 * evitando problemas de CORS no frontend.
 */

header('Content-Type: application/json; charset=utf-8');

// Permite apenas requisições GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => true, 'message' => 'Método não permitido']);
    exit;
}

// Obtém o CEP da query string
$cep = isset($_GET['cep']) ? $_GET['cep'] : '';

// Remove caracteres não numéricos
$cep = preg_replace('/[^0-9]/', '', $cep);

// Valida o CEP
if (strlen($cep) !== 8) {
    http_response_code(400);
    echo json_encode(['erro' => true, 'message' => 'CEP inválido. Deve conter 8 dígitos.']);
    exit;
}

// Monta a URL da API ViaCEP
$url = "https://viacep.com.br/ws/{$cep}/json/";

// Faz a requisição para a API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Verifica se houve erro na requisição
if ($error) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'message' => 'Erro ao consultar o CEP']);
    exit;
}

// Verifica o código de resposta HTTP
if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['erro' => true, 'message' => 'Erro ao consultar o CEP']);
    exit;
}

// Decodifica a resposta
$data = json_decode($response, true);

// Verifica se o CEP foi encontrado
if (isset($data['erro']) && $data['erro'] === true) {
    http_response_code(404);
    echo json_encode(['erro' => true, 'message' => 'CEP não encontrado']);
    exit;
}

// Retorna os dados do CEP
echo json_encode($data);
