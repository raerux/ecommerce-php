<?php
/**
 * Configuração de Conexão com o Banco de Dados
 * 
 * Este arquivo contém as configurações de conexão PDO com MySQL.
 * Certifique-se de alterar as credenciais conforme seu ambiente.
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Cria e retorna uma conexão PDO com o banco de dados
 * 
 * @return PDO Objeto de conexão PDO
 * @throws PDOException Em caso de erro na conexão
 */
function getConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Em produção, você deve logar o erro e mostrar uma mensagem genérica
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}
