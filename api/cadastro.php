<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/config.php';

// ============ VALIDAÇÕES DE MÉTODO E SESSÃO ============

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// ============ RECEBER E SANITIZAR DADOS ============

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$nome_empresa = trim($_POST['nome_empresa'] ?? '');
$ramo = trim($_POST['ramo'] ?? 'Não especificado');
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';
$termos = $_POST['termos'] ?? '';
$plano_id = (int)($_POST['plano_id'] ?? 1);

// ============ VALIDAÇÕES ============

// Validar campos obrigatórios
if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($termos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
    exit;
}

// Validar comprimento do nome
if (strlen($nome) < 3 || strlen($nome) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nome deve ter entre 3 e 100 caracteres']);
    exit;
}

// Validar telefone (flexível)
$telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);
if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Telefone inválido (mínimo 10 dígitos)']);
    exit;
}

// Validar senha
if (strlen($senha) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres']);
    exit;
}

if (strlen($senha) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A senha é muito longa']);
    exit;
}

// Validar confirmação de senha
if ($senha !== $confirmar_senha) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
    exit;
}

// Validar plano
if (!isset(PLANOS[$plano_id])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Plano inválido']);
    exit;
}

// Usar nome como nome_empresa se não informado
if (empty($nome_empresa)) {
    $nome_empresa = $nome;
}

// Validar nome_empresa
if (strlen($nome_empresa) < 3 || strlen($nome_empresa) > 150) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nome da empresa deve ter entre 3 e 150 caracteres']);
    exit;
}

// Validar ramo
if (strlen($ramo) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ramo muito longo']);
    exit;
}

try {
    // ============ VERIFICAR SE EMAIL JÁ EXISTE ============
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Este e-mail já está cadastrado']);
        exit;
    }
    $stmt->close();

    // ============ GERAR TOKEN E HASH DE SENHA ============
    $token_confirmacao = bin2hex(random_bytes(32));
    $senha_hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

    // ============ INICIAR TRANSAÇÃO ============
    $conn->begin_transaction();

    // ============ INSERIR USUÁRIO ============
    // Gravamos o token de confirmação diretamente na tabela `usuarios`.
    $stmt = $conn->prepare("
        INSERT INTO usuarios (
            nome,
            email,
            telefone,
            senha,
            tipo,
            status,
            data_cadastro,
            email_token,
            email_token_expires
        ) VALUES (?, ?, ?, ?, 'cliente', 'pendente_confirmacao', NOW(), ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
    ");

    if (!$stmt) {
        throw new Exception("Erro ao preparar insert usuário: " . $conn->error);
    }

    // bind: nome, email, telefone_limpo, senha_hash, token_confirmacao
    $stmt->bind_param("sssss", $nome, $email, $telefone_limpo, $senha_hash, $token_confirmacao);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir usuário: " . $stmt->error);
    }

    $usuario_id = $stmt->insert_id;
    $stmt->close();

    if ($usuario_id === 0) {
        throw new Exception("Falha ao obter ID do usuário inserido");
    }

    // ============ CRIAR REGISTRO DO CLIENTE ============
    $stmt = $conn->prepare("
        INSERT INTO clientes (
            user_id,
            nome_empresa,
            ramo,
            status,
            plano_id,
            data_criacao
        ) VALUES (?, ?, ?, 'aguardando_ativacao', ?, NOW())
    ");

    if (!$stmt) {
        throw new Exception("Erro ao preparar insert cliente: " . $conn->error);
    }

    $stmt->bind_param("issi", $usuario_id, $nome_empresa, $ramo, $plano_id);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir cliente: " . $stmt->error);
    }

    $cliente_id = $stmt->insert_id;
    $stmt->close();

    if ($cliente_id === 0) {
        throw new Exception("Falha ao obter ID do cliente inserido");
    }

    // ============ REGISTRAR LOG ============
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $plano_nome = PLANOS[$plano_id]['nome'] ?? 'Desconhecido';
    $descricao = "Novo usuário cadastrado - Plano: " . $plano_nome;

    $stmt = $conn->prepare("
        INSERT INTO logs (
            usuario_id,
            acao,
            descricao,
            ip_address,
            user_agent,
            created_at
        ) VALUES (?, 'cadastro', ?, ?, ?, NOW())
    ");

    if ($stmt) {
        $stmt->bind_param("isss", $usuario_id, $descricao, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
    }

    // ============ ENVIAR EMAIL DE CONFIRMAÇÃO ============
    $base_url = rtrim($_ENV['BASE_URL'] ?? 'https://sitesparaempresas.com', '/');
    $link_confirmacao = $base_url . "/confirmar-email.php?token=" . $token_confirmacao;

    $assunto = "Confirme seu e-mail - SitesParaEmpresas";

    $corpo = "
    <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; border-radius: 8px; }
                .button { display: inline-block; background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: bold; }
                .footer { font-size: 12px; color: #666; }
                hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bem-vindo ao SitesParaEmpresas!</h2>

                <p>Olá <strong>" . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>

                <p>Seu cadastro foi realizado com sucesso! Para ativar sua conta, confirme seu e-mail clicando no botão abaixo:</p>

                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . htmlspecialchars($link_confirmacao, ENT_QUOTES, 'UTF-8') . "' class='button'>
                        Confirmar E-mail
                    </a>
                </p>

                <p>Ou copie este link:</p>
                <p style='background: #e0e0e0; padding: 10px; border-radius: 4px; word-break: break-all;'>
                    " . htmlspecialchars($link_confirmacao, ENT_QUOTES, 'UTF-8') . "
                </p>

                <p><strong>Este link expira em 24 horas.</strong></p>

                <hr>

                <p class='footer'>
                    Se você não solicitou este cadastro, ignore este e-mail ou entre em contato com o suporte.
                </p>
            </div>
        </body>
    </html>
    ";

    // Use our new PHPMailer function
    $email_enviado = enviarEmail($email, $assunto, $corpo);

    if (!$email_enviado) {
        error_log("Aviso: Email não enviado para " . $email . " (usuário " . $usuario_id . ")");
        // Não falhamos por isso - o usuário pode resolicitar confirmação depois
    }

    // ============ CONFIRMAR TRANSAÇÃO ============
    $conn->commit();

    // ============ RESPOSTA DE SUCESSO ============
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Cadastro realizado com sucesso! Verifique seu e-mail para confirmar.',
        'data' => [
            'usuario_id' => (int)$usuario_id,
            'cliente_id' => (int)$cliente_id,
            'email' => $email,
            'plano' => $plano_nome
        ]
    ]);

} catch (Exception $e) {
    // ============ REVERTER TRANSAÇÃO EM CASO DE ERRO ============
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }

    error_log("Erro no cadastro: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar cadastro. Tente novamente mais tarde.',
        'debug' => (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : null
    ]);

} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>