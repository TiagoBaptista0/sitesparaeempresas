<?php
require '../config/db.php';
require '../config/functions.php';
require '../vendor/autoload.php';
require 'namecheap_helper.php';

use MercadoPago\SDK;
use MercadoPago\Payment;

header('Content-Type: application/json');

$mp_access_token = getenv('MP_ACCESS_TOKEN') ?: (defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : null);
if (!$mp_access_token) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'MP_ACCESS_TOKEN not configured']);
    exit;
}

SDK::setAccessToken($mp_access_token);

// Configurar SSL para ngrok
$cacert_path = getenv('CACERT_PATH') ?: (defined('CACERT_PATH') ? CACERT_PATH : 'C:/wamp64/bin/php/php8.3.14/cacert.pem');
if (file_exists($cacert_path)) {
    SDK::setSSLVerification($cacert_path);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    if ($input['type'] === 'payment') {
        $payment_id = $input['data']['id'] ?? null;

        if (!$payment_id) {
            throw new Exception('Payment ID not found');
        }

        $payment = Payment::find_by_id($payment_id);

        if (!$payment) {
            throw new Exception('Payment not found in Mercado Pago');
        }

        $external_reference = $payment->external_reference ?? null;

        if ($payment->status === 'approved') {
            $stmt = $pdo->prepare('
                SELECT p.*, u.id as user_id, u.email, u.first_name, u.last_name, u.phone, u.address, u.city, u.state, u.postal_code, u.country
                FROM pagamentos p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.mercadopago_payment_id = ? OR p.id = ?
                LIMIT 1
            ');
            $stmt->execute([$payment_id, $external_reference]);
            $pagamento = $stmt->fetch();

            if (!$pagamento) {
                throw new Exception('Payment record not found in database');
            }

            $usuario_id = $pagamento['user_id'];
            $dominio = $pagamento['dominio'] ?? null;

            $stmt = $pdo->prepare('
                UPDATE pagamentos 
                SET status = ?, mercadopago_status = ?, data_pagamento = NOW()
                WHERE id = ?
            ');
            $stmt->execute(['aprovado', $payment->status, $pagamento['id']]);

            if ($dominio) {
                $stmt = $pdo->prepare('
                    SELECT id FROM clientes 
                    WHERE user_id = ? AND dominio = ?
                    LIMIT 1
                ');
                $stmt->execute([$usuario_id, $dominio]);
                $cliente = $stmt->fetch();

                if ($cliente) {
                    $cliente_id = $cliente['id'];

                    $stmt = $pdo->prepare('
                        UPDATE clientes 
                        SET status = ?
                        WHERE id = ?
                    ');
                    $stmt->execute(['aguardando_dominio_registro', $cliente_id]);

                    registerDomainAutomatically($usuario_id, $dominio, $cliente_id, $pagamento);
                }
            }

            $stmt = $pdo->prepare('
                INSERT INTO assinaturas (usuario_id, plano_id, status, data_inicio, data_fim, data_proximo_pagamento, valor)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');

            $data_inicio = date('Y-m-d');
            $data_fim = date('Y-m-d', strtotime('+1 month'));
            $plano_id = $pagamento['plano_id'] ?? 1;
            $valor = $pagamento['valor_plano'] ?? $pagamento['valor'];

            $stmt->execute([
                $usuario_id,
                $plano_id,
                'ativa',
                $data_inicio,
                $data_fim,
                $data_fim,
                $valor
            ]);

            logActivity($usuario_id, 'pagamento_aprovado', 'Pagamento aprovado via Mercado Pago', [
                'payment_id' => $payment_id,
                'valor' => $pagamento['valor'],
                'dominio' => $dominio
            ]);

            // Enviar email de confirmação
            $subject = "Pagamento Confirmado - Sites Para Empresas";
            $body = "
            <h2>Pagamento Confirmado com Sucesso!</h2>
            <p>Olá " . htmlspecialchars($usuario['first_name'] ?? $usuario['nome']) . ",</p>
            <p>Seu pagamento foi processado com sucesso. Aqui estão os detalhes:</p>
            <ul>
                <li><strong>Domínio:</strong> " . htmlspecialchars($dominio) . "</li>
                <li><strong>Valor Pago:</strong> R$ " . number_format($pagamento['valor'], 2, ',', '.') . "</li>
                <li><strong>Data do Pagamento:</strong> " . date('d/m/Y H:i') . "</li>
            </ul>
            <p>Seu domínio será registrado em breve e você receberá instruções adicionais.</p>
            <p>Atenciosamente,<br>Equipe Sites Para Empresas</p>
            ";

            sendEmail($usuario['email'], $subject, $body);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment_id' => $payment_id,
                'status' => 'approved'
            ]);

        } elseif ($payment->status === 'pending') {
            $stmt = $pdo->prepare('
                UPDATE pagamentos 
                SET status = ?, mercadopago_status = ?
                WHERE mercadopago_payment_id = ? OR id = ?
            ');
            $stmt->execute(['pendente', $payment->status, $payment_id, $external_reference]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Payment pending',
                'payment_id' => $payment_id,
                'status' => 'pending'
            ]);

        } elseif ($payment->status === 'rejected' || $payment->status === 'cancelled') {
            $stmt = $pdo->prepare('
                UPDATE pagamentos 
                SET status = ?, mercadopago_status = ?
                WHERE mercadopago_payment_id = ? OR id = ?
            ');
            $stmt->execute(['recusado', $payment->status, $payment_id, $external_reference]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Payment rejected',
                'payment_id' => $payment_id,
                'status' => 'rejected'
            ]);
        }

    } elseif ($input['type'] === 'merchant_order') {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Merchant order received']);

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown notification type']);
    }

} catch (Exception $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function registerDomainAutomatically($usuario_id, $dominio, $cliente_id, $pagamento) {
    global $pdo;

    try {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            throw new Exception('User not found');
        }

        $contact = [
            'FirstName' => $usuario['first_name'] ?? 'Cliente',
            'LastName' => $usuario['last_name'] ?? 'Sitesparaempresas',
            'Address1' => $usuario['address'] ?? 'Rua Exemplo, 123',
            'City' => $usuario['city'] ?? 'São José do Rio Pardo',
            'StateProvince' => $usuario['state'] ?? 'SP',
            'PostalCode' => $usuario['postal_code'] ?? '13720000',
            'Country' => $usuario['country'] ?? 'BR',
            'Phone' => $usuario['phone'] ?? '+55.19999999999',
            'EmailAddress' => $usuario['email']
        ];

        $params = [
            'Command' => 'namecheap.domains.create',
            'DomainName' => $dominio,
            'Years' => 1,
        ];

        foreach (['Registrant', 'Tech', 'Admin', 'AuxBilling'] as $type) {
            foreach ($contact as $key => $value) {
                $params[$type . $key] = $value;
            }
        }

        $result = callNamecheapAPI($params);

        if (!$result['success']) {
            throw new Exception('Namecheap API error: ' . $result['message']);
        }

        $info = $result['xml']->CommandResponse->DomainCreateResult;
        $domainId = (string)$info['DomainID'];
        $orderId = (string)$info['OrderID'];

        $stmt = $pdo->prepare('
            UPDATE clientes
            SET status = ?, namecheap_domain_id = ?, namecheap_order_id = ?
            WHERE id = ?
        ');

        // Executa atualização do cliente com os dados do Namecheap
        $stmt->execute(['dominio_registrado', $domainId, $orderId, $cliente_id]);

        // Configura DNS automaticamente (função existente)
        configureDNSAutomatically($dominio, $cliente_id);

        // Registra atividade de domínio registrado
        logActivity($usuario_id, 'dominio_registrado', 'Domínio registrado automaticamente via Namecheap', [
            'dominio' => $dominio,
            'domain_id' => $domainId,
            'order_id' => $orderId
        ]);

        // Enviar email de confirmação de domínio registrado
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $nomeUsuario = htmlspecialchars($usuario['first_name'] ?? $usuario['nome'] ?? '');
            $emailUsuario = $usuario['email'] ?? null;

            if ($emailUsuario) {
                $subject = "Domínio Registrado - Sites Para Empresas";
                $body = "
                <h2>Domínio Registrado com Sucesso!</h2>
                <p>Olá " . $nomeUsuario . ",</p>
                <p>Seu domínio <strong>" . htmlspecialchars($dominio) . "</strong> foi registrado com sucesso!</p>
                <p>Dados do registro:</p>
                <ul>
                    <li><strong>Domínio:</strong> " . htmlspecialchars($dominio) . "</li>
                    <li><strong>ID do Domínio:</strong> " . htmlspecialchars($domainId) . "</li>
                    <li><strong>Data do Registro:</strong> " . date('d/m/Y H:i') . "</li>
                </ul>
                <p>Seu site estará disponível em breve após a configuração dos DNS.</p>
                <p>Atenciosamente,<br>Equipe Sites Para Empresas</p>
                ";

                // sendEmail existente no projeto
                sendEmail($emailUsuario, $subject, $body);
            }

            // Tentar configurar DNS e enviar confirmação de DNS configurado
            // Observação: configureDNSAutomatically pode ser idempotente; se já for chamada posteriormente, pode repetir
            try {
                // Se configureDNSAutomatically retornar os nameservers, capture-os; caso contrário, ficará nulo
                $nameservers = null;
                if (function_exists('configureDNSAutomatically')) {
                    $nameservers = configureDNSAutomatically($dominio, $cliente_id);
                } else {
                    // Fallback: chamar diretamente mesmo se não retornar nameservers
                    @configureDNSAutomatically($dominio, $cliente_id);
                }

                // Registrar atividade de DNS configurado
                if (function_exists('logActivity')) {
                    logActivity(null, 'dns_configurado', 'DNS configurado automaticamente para domínio', [
                        'domínio' => $dominio,
                        'nameservers' => $nameservers
                    ]);
                }

                // Enviar email de confirmação de DNS configurado
                // Primeiro buscar o usuário associado ao cliente
                $stmt = $pdo->prepare('SELECT u.* FROM usuarios u JOIN clientes c ON u.id = c.user_id WHERE c.id = ?');
                $stmt->execute([$cliente_id]);
                $usuario = $stmt->fetch();

                if ($usuario && !empty($usuario['email'])) {
                    $nomeUsuarioEmail = htmlspecialchars($usuario['first_name'] ?? $usuario['nome'] ?? '');
                    $subjectDns = "DNS Configurado - Sites Para Empresas";
                    $bodyDns = "
                    <h2>DNS Configurado com Sucesso!</h2>
                    <p>Olá " . $nomeUsuarioEmail . ",</p>
                    <p>O DNS para seu domínio <strong>" . htmlspecialchars($dominio) . "</strong> foi configurado com sucesso!</p>
                    <p>Seu site estará disponível em breve.</p>
                    <p>Atenciosamente,<br>Equipe Sites Para Empresas</p>
                    ";

                    // sendEmail já existe no projeto
                    sendEmail($usuario['email'], $subjectDns, $bodyDns);
                }
            } catch (Exception $e) {
                error_log('DNS configuration error: ' . $e->getMessage());
            }
        }
        $stmt->execute(['dominio_registrado', $domainId, $orderId, $cliente_id]);

        configureDNSAutomatically($dominio, $cliente_id);

        logActivity($usuario_id, 'dominio_registrado', 'Domínio registrado automaticamente via Namecheap', [
            'dominio' => $dominio,
            'domain_id' => $domainId,
            'order_id' => $orderId
        ]);

    } catch (Exception $e) {
        error_log('Domain registration error: ' . $e->getMessage());
        logActivity($usuario_id, 'dominio_erro_registro', 'Erro ao registrar domínio: ' . $e->getMessage(), [
            'dominio' => $dominio
        ]);
    }
}

function configureDNSAutomatically($dominio, $cliente_id) {
    global $pdo;

    try {
        $nameservers = getenv('NAMESERVERS') ?: 'dns1.hostinger.com,dns2.hostinger.com';

        $parts = explode('.', $dominio);
        if (count($parts) < 2) {
            throw new Exception('Invalid domain format');
        }

        $sld = $parts[0];
        $tld = implode('.', array_slice($parts, 1));

        $result = callNamecheapAPI([
            'Command' => 'namecheap.domains.dns.setCustom',
            'SLD' => $sld,
            'TLD' => $tld,
            'Nameservers' => $nameservers
        ]);

        if (!$result['success']) {
            throw new Exception('Namecheap DNS API error: ' . $result['message']);
        }

        $stmt = $pdo->prepare('
            UPDATE clientes 
            SET status = ?
            WHERE id = ?
        ');
        $stmt->execute(['dns_configurado', $cliente_id]);

        logActivity(null, 'dns_configurado', 'DNS configurado automaticamente para domínio', [
            'dominio' => $dominio,
            'nameservers' => $nameservers
        ]);

    } catch (Exception $e) {
        error_log('DNS configuration error: ' . $e->getMessage());
    }
}

function logActivity($usuario_id, $acao, $descricao, $dados_adicionais = []) {
    global $pdo;

    try {
        $stmt = $pdo->prepare('
            INSERT INTO logs (usuario_id, acao, descricao, ip_address, dados_adicionais)
            VALUES (?, ?, ?, ?, ?)
        ');

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $dados_json = json_encode($dados_adicionais);

        $stmt->execute([$usuario_id, $acao, $descricao, $ip, $dados_json]);
    } catch (Exception $e) {
        error_log('Log activity error: ' . $e->getMessage());
    }
}
?>