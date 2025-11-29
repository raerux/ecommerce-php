-- =============================================
-- E-Commerce PHP - Script de Criação do Banco
-- =============================================

-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ecommerce_db;

-- =============================================
-- Tabela de Clientes
-- =============================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    telefone VARCHAR(15),
    cep VARCHAR(9),
    endereco VARCHAR(200),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela de Produtos
-- =============================================
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    categoria VARCHAR(50),
    imagem VARCHAR(500),
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela de Compras
-- =============================================
CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_produtos DECIMAL(10, 2) NOT NULL,
    valor_frete DECIMAL(10, 2) NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    cep_destino VARCHAR(9),
    status VARCHAR(50) DEFAULT 'Pendente',
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela de Itens da Compra
-- =============================================
CREATE TABLE IF NOT EXISTS itens_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    INDEX idx_compra (compra_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Dados de Exemplo - Produtos
-- =============================================
INSERT INTO produtos (nome, descricao, preco, estoque, categoria, imagem) VALUES
('Smartphone Galaxy S23', 'Smartphone Samsung Galaxy S23 com 128GB de armazenamento, câmera de 50MP e tela AMOLED de 6.1 polegadas.', 3499.90, 50, 'Eletrônicos', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400'),
('Notebook Dell Inspiron', 'Notebook Dell Inspiron 15 com processador Intel Core i5, 8GB RAM e SSD de 256GB.', 2899.90, 30, 'Eletrônicos', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400'),
('Fone de Ouvido Bluetooth', 'Fone de ouvido sem fio com cancelamento de ruído ativo e bateria de 30 horas.', 299.90, 100, 'Eletrônicos', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400'),
('Camiseta Básica Preta', 'Camiseta de algodão 100% premium, corte regular, ideal para uso diário.', 59.90, 200, 'Roupas', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400'),
('Tênis Running Pro', 'Tênis esportivo para corrida com tecnologia de amortecimento e sola de borracha.', 349.90, 75, 'Calçados', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400'),
('Mochila Executiva', 'Mochila para notebook até 15.6 polegadas com compartimento acolchoado e porta USB.', 189.90, 45, 'Acessórios', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400'),
('Relógio Smartwatch', 'Smartwatch com monitor cardíaco, GPS integrado e resistência à água.', 799.90, 40, 'Eletrônicos', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400'),
('Calça Jeans Slim', 'Calça jeans com elastano para maior conforto, cor azul escuro.', 149.90, 120, 'Roupas', 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=400'),
('Cadeira Gamer RGB', 'Cadeira gamer ergonômica com iluminação RGB, apoio de braços ajustável e almofadas para lombar e pescoço.', 1299.90, 25, 'Móveis', 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?w=400'),
('Mouse Gamer', 'Mouse gamer com sensor óptico de 16000 DPI e iluminação RGB personalizável.', 199.90, 80, 'Eletrônicos', 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400'),
('Teclado Mecânico', 'Teclado mecânico com switches blue, iluminação RGB e layout ABNT2.', 349.90, 60, 'Eletrônicos', 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=400'),
('Perfume Masculino 100ml', 'Perfume masculino com notas amadeiradas e especiarias, fixação de longa duração.', 259.90, 35, 'Perfumaria', 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=400');
