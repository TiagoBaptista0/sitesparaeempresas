-- Database Schema for Sites Para Empresas
-- Criado para MySQL 5.7+

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS sitesparaempresas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sitesparaempresas;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo ENUM('cliente', 'admin') DEFAULT 'cliente',
    status ENUM('ativo', 'inativo', 'suspenso', 'pendente_confirmacao') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL,
    email_confirmed BOOLEAN DEFAULT FALSE,
    email_token VARCHAR(64) NULL,
    email_token_expires TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_email_token (email_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de planos
CREATE TABLE IF NOT EXISTS planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    periodo ENUM('mensal', 'anual') DEFAULT 'mensal',
    recursos JSON,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de assinaturas
CREATE TABLE IF NOT EXISTS assinaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    plano_id INT NOT NULL,
    status ENUM('ativa', 'cancelada', 'suspensa', 'expirada') DEFAULT 'ativa',
    data_inicio DATE NOT NULL,
    data_fim DATE,
    data_proximo_pagamento DATE,
    valor DECIMAL(10,2) NOT NULL,
    mercadopago_subscription_id VARCHAR(100),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES planos(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_data_fim (data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nome_empresa VARCHAR(100),
    ramo VARCHAR(100),
    dominio VARCHAR(100) UNIQUE,
    status ENUM('aguardando_email', 'aguardando_dominio', 'ativo', 'inativo', 'cancelado') DEFAULT 'aguardando_email',
    plano_id INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_dominio (dominio),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de pagamentos
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    assinatura_id INT,
    valor DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'aprovado', 'recusado', 'cancelado', 'estornado') DEFAULT 'pendente',
    metodo_pagamento VARCHAR(50),
    mercadopago_payment_id VARCHAR(100),
    mercadopago_status VARCHAR(50),
    data_pagamento TIMESTAMP NULL,
    data_vencimento DATE,
    descricao TEXT,
    dados_pagamento JSON,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_mercadopago (mercadopago_payment_id),
    INDEX idx_data_pagamento (data_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de confirmação de email
CREATE TABLE IF NOT EXISTS email_confirmacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_expiracao TIMESTAMP NOT NULL,
    utilizado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario (usuario_id),
    INDEX idx_expiracao (data_expiracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de sites
CREATE TABLE IF NOT EXISTS sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    dominio VARCHAR(100) UNIQUE,
    subdominio VARCHAR(50) UNIQUE,
    preference_id VARCHAR(255) NULL,
    template VARCHAR(50) DEFAULT 'padrao',
    status ENUM('ativo', 'inativo', 'em_construcao') DEFAULT 'em_construcao',
    configuracoes JSON,
    conteudo JSON,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_publicacao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_dominio (dominio),
    INDEX idx_subdominio (subdominio),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tickets de suporte
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    assunto VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('aberto', 'em_andamento', 'resolvido', 'fechado') DEFAULT 'aberto',
    prioridade ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media',
    categoria VARCHAR(50),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_fechamento TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de respostas de tickets
CREATE TABLE IF NOT EXISTS ticket_respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de atividades
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    dados_adicionais JSON,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_acao (acao),
    INDEX idx_data (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir planos padrão
INSERT INTO planos (nome, descricao, preco, periodo, recursos) VALUES
('Básico', 'Plano ideal para começar', 29.90, 'mensal', '{"paginas": 5, "armazenamento": "1GB", "suporte": "email", "ssl": true, "dominio_proprio": false}'),
('Profissional', 'Para empresas em crescimento', 59.90, 'mensal', '{"paginas": 15, "armazenamento": "5GB", "suporte": "prioritario", "ssl": true, "dominio_proprio": true, "email_profissional": 5}'),
('Empresarial', 'Solução completa para sua empresa', 99.90, 'mensal', '{"paginas": "ilimitado", "armazenamento": "20GB", "suporte": "24/7", "ssl": true, "dominio_proprio": true, "email_profissional": "ilimitado", "backup_diario": true}');

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo, status) VALUES
('Administrador', 'admin@sitesparaempresas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'ativo');

-- Criar views úteis
CREATE OR REPLACE VIEW view_assinaturas_ativas AS
SELECT 
    a.id,
    a.usuario_id,
    u.nome as usuario_nome,
    u.email as usuario_email,
    p.nome as plano_nome,
    a.valor,
    a.data_inicio,
    a.data_fim,
    a.data_proximo_pagamento,
    a.status
FROM assinaturas a
JOIN usuarios u ON a.usuario_id = u.id
JOIN planos p ON a.plano_id = p.id
WHERE a.status = 'ativa';

CREATE OR REPLACE VIEW view_pagamentos_recentes AS
SELECT 
    p.id,
    p.usuario_id,
    u.nome as usuario_nome,
    u.email as usuario_email,
    p.valor,
    p.status,
    p.metodo_pagamento,
    p.data_pagamento,
    p.data_criacao
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
ORDER BY p.data_criacao DESC;

-- Mensagem de sucesso
SELECT 'Database schema criado com sucesso!' as message;
