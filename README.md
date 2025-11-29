# 🛒 E-Commerce PHP

Sistema completo de e-commerce desenvolvido em PHP e MySQL, com foco em simplicidade e funcionalidade.

## 📋 Descrição

Aplicação web de e-commerce completa com:
- Sistema de autenticação de clientes
- Catálogo de produtos com filtro por categoria
- Carrinho de compras com sessão PHP
- Integração com API ViaCEP para busca de endereço
- Cálculo de frete automatizado
- Histórico de pedidos
- Área administrativa para gestão de produtos e pedidos

## 🚀 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Extensão PDO habilitada
- Extensão cURL habilitada

## ⚙️ Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/ecommerce-php.git
cd ecommerce-php
```

### 2. Configure o banco de dados

1. Crie um banco de dados MySQL:
```sql
CREATE DATABASE ecommerce_db;
```

2. Importe o script SQL:
```bash
mysql -u root -p ecommerce_db < database.sql
```

### 3. Configure a conexão

Edite o arquivo `config/database.php` com suas credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 4. Inicie o servidor PHP

```bash
php -S localhost:8000
```

Acesse: http://localhost:8000

## 🔐 Credenciais Padrão

### Área Administrativa
- **URL:** `/admin/login.php`
- **Email:** `admin@admin.com`
- **Senha:** `admin123`

## 📁 Estrutura de Arquivos

```
/
├── config/
│   └── database.php          # Conexão PDO com MySQL
├── css/
│   └── style.css             # Estilos responsivos
├── js/
│   ├── main.js               # Funções gerais
│   └── carrinho.js           # Manipulação do carrinho
├── admin/
│   ├── index.php             # Dashboard administrativo
│   ├── login.php             # Login admin
│   ├── logout.php            # Logout admin
│   ├── produtos.php          # CRUD de produtos
│   ├── compras.php           # Lista de compras
│   └── includes/
│       ├── admin_header.php  # Header do admin
│       └── admin_footer.php  # Footer do admin
├── includes/
│   ├── header.php            # Cabeçalho comum
│   ├── footer.php            # Rodapé comum
│   └── functions.php         # Funções auxiliares
├── api/
│   └── cep.php               # Proxy para API ViaCEP
├── index.php                 # Catálogo de produtos
├── cadastro.php              # Cadastro de cliente
├── login.php                 # Login de cliente
├── logout.php                # Logout
├── carrinho.php              # Carrinho de compras
├── checkout.php              # Finalização de compra
├── pedidos.php               # Histórico de pedidos
├── database.sql              # Script SQL
└── README.md                 # Este arquivo
```

## 🎯 Funcionalidades

### Área Pública

- **Catálogo de Produtos**
  - Grid responsivo com produtos
  - Filtro por categoria
  - Exibição de estoque
  - Botão de adicionar ao carrinho

- **Carrinho de Compras**
  - Adicionar/remover itens
  - Alterar quantidade
  - Cálculo de subtotais
  - Persistência na sessão

- **Checkout**
  - Busca automática de CEP via ViaCEP
  - Cálculo de frete
  - Resumo do pedido
  - Validação de estoque

- **Conta do Cliente**
  - Cadastro com validação
  - Login/logout
  - Histórico de pedidos

### Área Administrativa

- **Dashboard**
  - Estatísticas gerais
  - Últimas compras
  - Produtos com baixo estoque

- **Gestão de Produtos**
  - Cadastrar novo produto
  - Editar produto
  - Remover produto (soft delete)
  - Listagem completa

- **Gestão de Pedidos**
  - Visualizar todos os pedidos
  - Filtrar por status
  - Atualizar status
  - Ver detalhes do pedido

## 💰 Cálculo de Frete

O frete é calculado automaticamente baseado no CEP:
- **Frete base:** R$ 10,00
- **Adicional:** R$ 2,00 por cada dígito par no CEP

Exemplo: CEP 01310-100 possui 4 dígitos pares (0, 0, 0) = R$ 10 + (4 × R$ 2) = **R$ 18,00**

## 🔒 Segurança

- Proteção contra SQL Injection (prepared statements)
- Proteção contra XSS (htmlspecialchars)
- Senhas armazenadas com hash (password_hash)
- Tokens CSRF em formulários críticos
- Validação de dados no servidor

## 📱 Responsividade

O sistema é totalmente responsivo e funciona bem em:
- Desktop
- Tablet
- Mobile

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 7.4+
- **Banco de Dados:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **API Externa:** ViaCEP

## 📝 Licença

Este projeto está sob a licença MIT.
