<?php
// domain_search.php
// Versão corrigida com validações e segurança melhoradas

session_start();

// Validar autenticação (descomente se necessário)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// Carregar configuração do banco de dados
$db_path = __DIR__ . '/config/db.php';
if (!file_exists($db_path)) {
    die('Erro: arquivo config/db.php não encontrado.');
}
require_once $db_path;

// Preços padrão por TLD
$tld_prices = [
    'com.br' => 39.00,
    'net.br' => 49.00,
    'com'    => 49.00,
    'net'    => 59.00,
    'org'    => 49.00,
    'info'   => 35.00,
];

// Lista de TLDs válidos em ordem de prioridade (mais específicos primeiro)
$valid_tlds = ['com.br', 'net.br', 'com', 'net', 'org', 'info'];

/**
 * Normaliza o domínio informado para formato consistente
 */
function normalize_domain($domain) {
    $domain = trim($domain);
    $domain = strtolower($domain);
    $domain = preg_replace('/^https?:\/\//', '', $domain); // remove protocolo
    $domain = preg_replace('/^www\./', '', $domain); // remove www
    $domain = preg_replace('/\/$/', '', $domain); // remove barra final
    $domain = preg_replace('/[^a-z0-9.\-]/', '', $domain); // apenas caracteres válidos
    return $domain;
}

/**
 * Detecta o TLD correto a partir da lista de domínios válidos
 */
function detect_tld($domain, $valid_tlds) {
    foreach ($valid_tlds as $tld) {
        if (substr($domain, -strlen($tld)) === $tld && substr($domain, -strlen($tld) - 1, 1) === '.') {
            return $tld;
        }
    }
    
    // Fallback: pega os últimos 2 componentes
    $parts = explode('.', $domain);
    if (count($parts) >= 2) {
        return implode('.', array_slice($parts, -2));
    }
    
    return end($parts) ?: 'com';
}

/**
 * Valida se o domínio tem formato correto
 */
function validate_domain($domain) {
    // Deve ter pelo menos um ponto e componentes válidos
    if (!preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]\.[a-z\.]{2,}$|^[a-z0-9]\.[a-z\.]{2,}$/', $domain)) {
        return false;
    }
    
    // Não pode ter hífens duplos ou começar/terminar com hífen
    $parts = explode('.', $domain);
    foreach ($parts as $part) {
        if (strpos($part, '--') !== false || $part[0] === '-' || $part[strlen($part) - 1] === '-') {
            return false;
        }
    }
    
    return true;
}

// Obter chave da API WHOIS
$whois_key = getenv('WHOISXMLAPI_KEY') ?: (defined('WHOISXMLAPI_KEY') ? WHOISXMLAPI_KEY : null);

// ============ ENDPOINT AJAX: BUSCAR DISPONIBILIDADE ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search') {
    header('Content-Type: application/json; charset=utf-8');

    $raw = $_POST['domain'] ?? '';
    $domain = normalize_domain($raw);

    // Validação
    if (empty($domain) || !validate_domain($domain)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Domínio inválido. Exemplo: meu-negocio.com.br'
        ]);
        exit;
    }

    // Detectar TLD
    $tld = detect_tld($domain, $valid_tlds);
    $price = $tld_prices[$tld] ?? 39.00;

    $available = null;
    $provider = null;
    $note = '';

    // 1) Tentar provider WHOISXMLAPI (se chave configurada)
    if (!empty($whois_key) && function_exists('curl_init')) {
        $provider = 'whoisxmlapi';
        $url = "https://www.whoisxmlapi.com/whoisserver/WhoisService?domainName=" . urlencode($domain) . 
               "&outputFormat=JSON&apiKey=" . urlencode($whois_key);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || empty($body)) {
            $note = 'Falha ao consultar provedor WHOIS. Usando método alternativo.';
            $available = null;
        } else {
            $data = json_decode($body, true);
            if (isset($data['WhoisRecord'])) {
                $registered = !empty($data['WhoisRecord']['registryData']) || 
                             !empty($data['WhoisRecord']['registrant']) || 
                             !empty($data['WhoisRecord']['rawText']);
                $available = !$registered;
                $note = 'Verificado via WHOIS API.';
            } else {
                $available = null;
                $note = 'Provedor retornou resposta incompleta.';
            }
        }
    }

    // 2) Fallback: verificação por DNS e whois local
    if ($available === null) {
        $provider = 'fallback';
        
        if (function_exists('checkdnsrr')) {
            if (checkdnsrr($domain, 'A') || checkdnsrr($domain, 'NS') || checkdnsrr($domain, 'MX')) {
                $available = false;
                $note = 'Domínio resolve via DNS (provável que esteja registrado).';
            } else {
                // Tentar whois via shell
                if (function_exists('shell_exec')) {
                    $whois_output = @shell_exec('whois ' . escapeshellarg($domain) . ' 2>/dev/null');
                    
                    if ($whois_output && preg_match('/(No match|NOT FOUND|No entries found|Status: free|no data found)/i', $whois_output)) {
                        $available = true;
                        $note = 'Verificado via whois local — Disponível.';
                    } elseif ($whois_output) {
                        $available = false;
                        $note = 'Verificado via whois local — Registrado.';
                    } else {
                        $available = null;
                        $note = 'Não foi possível executar whois. Resultado aproximado.';
                    }
                } else {
                    $available = null;
                    $note = 'whois/shell não disponível no servidor.';
                }
            }
        } else {
            $available = null;
            $note = 'Função checkdnsrr indisponível. Disponibilidade desconhecida.';
        }
    }

    // Resposta final
    $response = [
        'success' => true,
        'domain' => $domain,
        'tld' => $tld,
        'price' => number_format((float)$price, 2, '.', ''),
        'available' => $available,
        'provider' => $provider,
        'note' => $note,
    ];

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buscar Domínio — Sites Para Empresas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
        }

        .search-card {
            background: #fff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 24px;
        }

        .form-row {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .form-row input,
        .form-row select {
            padding: 12px 14px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-row input {
            flex: 1;
            min-width: 150px;
        }

        .form-row input:focus,
        .form-row select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row select {
            min-width: 180px;
        }

        .btn {
            background: #667eea;
            color: #fff;
            padding: 12px 24px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.3s, transform 0.2s;
        }

        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .result {
            margin-top: 20px;
            padding: 16px 18px;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .result strong {
            font-size: 18px;
            display: block;
            margin-bottom: 6px;
        }

        .available {
            background: #ecfdf5;
            border-left-color: #10b981;
            color: #065f46;
        }

        .unavailable {
            background: #fef2f2;
            border-left-color: #ef4444;
            color: #7f1d1d;
        }

        .unknown {
            background: #fffbeb;
            border-left-color: #f59e0b;
            color: #92400e;
        }

        .small {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
            line-height: 1.5;
        }

        .result .small {
            color: inherit;
            opacity: 0.9;
        }

        .result-details {
            margin-top: 12px;
            font-size: 13px;
            padding-top: 12px;
            border-top: 1px solid currentColor;
            opacity: 0.8;
        }

        .price-display {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            margin-top: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 14px;
        }

        .reserve-btn {
            background: #10b981;
            color: #fff;
            padding: 10px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s;
        }

        .reserve-btn:hover {
            background: #059669;
        }

        .reserve-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .loading {
            color: #667eea;
            font-style: italic;
        }

        @media (max-width: 600px) {
            .search-card {
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }

            .form-row {
                flex-direction: column;
            }

            .form-row input,
            .form-row select,
            .btn {
                width: 100%;
            }

            .form-row select {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-card">
            <h2>🔍 Buscar Domínio</h2>
            <p class="subtitle">Digite o nome que deseja (ex: seunegocio) e escolha a terminação</p>

            <div class="form-row">
                <input 
                    type="text" 
                    id="domainName" 
                    placeholder="seunegocio"
                    maxlength="63"
                >
                <select id="tldSelect">
                    <option value="com.br">.com.br — R$ 39,00/ano</option>
                    <option value="net.br">.net.br — R$ 49,00/ano</option>
                    <option value="com">.com — R$ 49,00/ano</option>
                    <option value="net">.net — R$ 59,00/ano</option>
                    <option value="org">.org — R$ 49,00/ano</option>
                    <option value="info">.info — R$ 35,00/ano</option>
                </select>
                <button id="searchBtn" class="btn">Buscar</button>
            </div>

            <div id="resultArea"></div>
        </div>
    </div>

    <script>
        const searchBtn = document.getElementById('searchBtn');
        const domainInput = document.getElementById('domainName');
        const tldSelect = document.getElementById('tldSelect');
        const resultArea = document.getElementById('resultArea');

        searchBtn.addEventListener('click', performSearch);
        domainInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') performSearch();
        });

        async function performSearch() {
            const name = domainInput.value.trim();
            const tld = tldSelect.value;

            if (!name) {
                showError('Digite o nome do domínio.');
                return;
            }

            // Limpar caracteres inválidos
            const cleanName = name.replace(/[^a-z0-9\-]/gi, '').toLowerCase();
            if (!cleanName) {
                showError('Nome de domínio inválido. Use apenas letras, números e hífens.');
                return;
            }

            const fullDomain = cleanName + '.' + tld;
            resultArea.innerHTML = '<div class="result loading">⏳ Consultando disponibilidade...</div>';
            searchBtn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('action', 'search');
                formData.append('domain', fullDomain);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    showError('Erro na requisição. Tente novamente.');
                    searchBtn.disabled = false;
                    return;
                }

                const data = await response.json();

                if (!data.success) {
                    showError(data.message || 'Erro ao consultar disponibilidade.');
                    searchBtn.disabled = false;
                    return;
                }

                displayResult(data);
            } catch (err) {
                console.error('Erro:', err);
                showError('Erro ao conectar com o servidor. Verifique sua conexão.');
                searchBtn.disabled = false;
            }
        }

        function displayResult(data) {
            searchBtn.disabled = false;
            let html = '';
            const price = parseFloat(data.price).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (data.available === true) {
                html += `
                    <div class="result available">
                        <strong>✅ Disponível!</strong>
                        <div class="small">Este domínio está livre para registro.</div>
                        <div class="price-display">R$ ${price}/ano</div>
                    </div>
                    <div class="action-buttons">
                        <button class="reserve-btn" onclick="reserveDomain('${data.domain}')">
                            Reservar Domínio
                        </button>
                    </div>
                `;
            } else if (data.available === false) {
                html += `
                    <div class="result unavailable">
                        <strong>❌ Indisponível</strong>
                        <div class="small">Este domínio já está registrado por outro usuário.</div>
                    </div>
                `;
            } else {
                html += `
                    <div class="result unknown">
                        <strong>⚠️ Indeterminado</strong>
                        <div class="small">Não foi possível verificar a disponibilidade com precisão. ${data.note || ''}</div>
                        <div class="price-display">R$ ${price}/ano</div>
                    </div>
                `;
            }

            html += `
                <div class="result-details">
                    <strong>Detalhes da verificação:</strong><br>
                    Provedor: ${data.provider || 'N/A'}<br>
                    TLD: ${data.tld}<br>
                    Nota: ${data.note || 'Sem informações adicionais'}
                </div>
            `;

            resultArea.innerHTML = html;
        }

        function showError(message) {
            searchBtn.disabled = false;
            resultArea.innerHTML = `
                <div class="result unknown">
                    <strong>⚠️ Erro</strong>
                    <div class="small">${message}</div>
                </div>
            `;
        }

        async function reserveDomain(domain) {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Reservando...';

            try {
                const response = await fetch('api/reserve-domain.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ domain: domain })
                });

                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }

                const data = await response.json();

                if (data.success) {
                    alert(`✅ Domínio ${domain} reservado por ${data.reserved_minutes || 15} minutos!\n\nProssiga para confirmar seu pedido.`);
                    window.location.href = `planos.php?selected_domain=${encodeURIComponent(domain)}`;
                } else {
                    alert(`❌ Erro ao reservar: ${data.message || 'Erro desconhecido'}`);
                    btn.disabled = false;
                    btn.textContent = 'Reservar Domínio';
                }
            } catch (err) {
                console.error('Erro:', err);
                alert('❌ Erro ao reservar domínio. Verifique sua conexão e tente novamente.');
                btn.disabled = false;
                btn.textContent = 'Reservar Domínio';
            }
        }
    </script>
</body>
</html>