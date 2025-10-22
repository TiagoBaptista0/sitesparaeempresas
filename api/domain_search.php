<?php
// domain_search.php
// Colocar este arquivo na raiz do projeto ou em /dashboard conforme sua estrutura.
// Requisitos opcionais: definir WHOISXMLAPI_KEY no .env ou via getenv().

session_start();
require_once 'config/db.php'; // ajuste o caminho se necessário

// Preços padrão por TLD (você pode alterar)
$tld_prices = [
    'com.br' => 39.00,
    'com'    => 49.00,
    'net'    => 59.00,
    'org'    => 49.00,
];

// Converte domínio informado para formato consistente
function normalize_domain($domain) {
    $domain = trim($domain);
    $domain = strtolower($domain);
    $domain = preg_replace('/^https?:\\/\\//', '', $domain);
    $domain = preg_replace('/^www\\./', '', $domain);
    $domain = preg_replace('/[^a-z0-9.\\-]/', '', $domain);
    return $domain;
}

// Verifica se há uma chave WHOISXMLAPI definida
$whois_key = getenv('WHOISXMLAPI_KEY') ?: (defined('WHOISXMLAPI_KEY') ? WHOISXMLAPI_KEY : null);

// Endpoint AJAX: busca
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search') {
    header('Content-Type: application/json; charset=utf-8');

    $raw = $_POST['domain'] ?? '';
    $domain = normalize_domain($raw);

    // validação rápida
    if (empty($domain) || !preg_match('/^[a-z0-9\\-]+\\.[a-z\\.]{2,}$/', $domain)) {
        echo json_encode(['success' => false, 'message' => 'Domínio inválido. Ex.: meu-negocio.com.br']);
        exit;
    }

    // detectar TLD
    $parts = explode('.', $domain);
    $tld = implode('.', array_slice($parts, -2)); // ex: exemplo.com.br => com.br
    if (!isset($tld_prices[$tld])) {
        // se não encontrado como último 2 níveis, tentar último nível
        $tld_single = end($parts);
        $tld = $tld_single;
    }
    $price = $tld_prices[$tld] ?? 39.00;

    $available = null;
    $provider = null;
    $note = '';

    // 1) Tentar provider WHOISXMLAPI (se chave configurada)
    if (!empty($whois_key) && function_exists('curl_init')) {
        $provider = 'whoisxmlapi';
        $url = "https://www.whoisxmlapi.com/whoisserver/WhoisService?domainName=" . urlencode($domain) . "&outputFormat=JSON&apiKey=" . urlencode($whois_key);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || empty($body)) {
            $note = 'Falha ao consultar provedor WHOIS (cURL). Resultado aproximado será retornado.';
        } else {
            $data = json_decode($body, true);
            // heurística: se veio WhoisRecord com dados, provavelmente registrado
            if (!empty($data['WhoisRecord'])) {
                // Se existem campos de registro, consideramos como registrado
                $registered = false;
                if (!empty($data['WhoisRecord']['registryData']) || !empty($data['WhoisRecord']['registrant']) || !empty($data['WhoisRecord']['rawText'])) {
                    $registered = true;
                }
                $available = !$registered;
                $note = 'Resultado proveniente do WHOIS provider.';
            } else {
                // se resposta não contém WhoisRecord, indicar desconhecido
                $available = null;
                $note = 'Provedor retornou resposta incompleta; disponibilidade desconhecida.';
            }
        }
    } else {
        // 2) Fallback: tentativa básica por DNS / whois do sistema (apróx.)
        // checkdnsrr é somente heurística (se o domínio aponta DNS, quase certo que está registrado)
        $provider = 'fallback';
        if (function_exists('checkdnsrr')) {
            // procura registros A ou NS
            if (checkdnsrr($domain, 'A') || checkdnsrr($domain, 'NS') || checkdnsrr($domain, 'MX')) {
                $available = false; // provavelmente registrado
                $note = 'Domínio resolve via DNS — provável que esteja registrado (checagem aproximada).';
            } else {
                // tentar whois via shell, se disponível
                if (function_exists('shell_exec')) {
                    $whois_out = @shell_exec('whois ' . escapeshellarg($domain) . ' 2>/dev/null');
                    if ($whois_out && preg_match('/(No match|NOT FOUND|No entries found|Status: free|no data found)/i', $whois_out)) {
                        $available = true;
                        $note = 'whois local indica domínio disponível (apróx.).';
                    } elseif ($whois_out) {
                        $available = false;
                        $note = 'whois local indica domínio registrado (apróx.).';
                    } else {
                        $available = null;
                        $note = 'Não foi possível executar whois local; resultado aproximado via DNS retornado.';
                    }
                } else {
                    $available = null;
                    $note = 'Ambiente sem whois/shell disponível; resultado aproximado via DNS retornado.';
                }
            }
        } else {
            $available = null;
            $note = 'Função checkdnsrr indisponível no servidor; disponibilidade desconhecida.';
        }
    }

    // Resultado final
    $response = [
        'success' => true,
        'domain' => $domain,
        'tld' => $tld,
        'price' => number_format((float)$price, 2, '.', ''),
        'available' => $available, // true/false/null
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
<title>Buscar Domínio — Sites Para Empresas</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
.container { max-width:900px; margin:40px auto; font-family: Arial, sans-serif; padding:0 16px; }
.search-card { background:#fff; padding:24px; border-radius:8px; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
.form-row { display:flex; gap:10px; margin-bottom:12px; }
.form-row input, .form-row select { padding:12px; font-size:16px; border:1px solid #ddd; border-radius:6px; flex:1; }
.btn { background:#007bff; color:#fff; padding:12px 18px; border-radius:6px; border:none; cursor:pointer; font-weight:600; }
.result { margin-top:16px; padding:16px; border-radius:8px; }
.available { background:#e6ffed; border:1px solid #b5f0c9; color:#1a7f31; }
.unavailable { background:#fff0f0; border:1px solid #f5c2c2; color:#a11; }
.unknown { background:#fff9e6; border:1px solid #ffe7a8; color:#7a5a00; }
.small { font-size:13px; color:#666; margin-top:6px; }
.reserve-btn { background:#28a745; color:#fff; padding:10px 14px; border-radius:6px; border:none; cursor:pointer; }
</style>
</head>
<body>
<div class="container">
    <div class="search-card">
        <h2>Buscar domínio</h2>
        <p class="small">Digite o domínio que deseja (ex: meusite) e escolha a terminação.</p>

        <div class="form-row">
            <input type="text" id="domainName" placeholder="seunegocio" value="">
            <select id="tldSelect">
                <option value="com.br">.com.br — R$ 39 / ano</option>
                <option value="com">.com — R$ 49 / ano</option>
                <option value="net">.net — R$ 59 / ano</option>
                <option value="org">.org — R$ 49 / ano</option>
            </select>
            <button id="searchBtn" class="btn">Buscar</button>
        </div>

        <div id="resultArea"></div>
    </div>
</div>

<script>
document.getElementById('searchBtn').addEventListener('click', async function() {
    const name = document.getElementById('domainName').value.trim();
    const tld = document.getElementById('tldSelect').value;
    if (!name) {
        alert('Digite o nome do domínio.');
        return;
    }
    const full = name.replace(/^\\.|\\s+/g, '') + '.' + tld;
    const resultArea = document.getElementById('resultArea');
    resultArea.innerHTML = '<div class="small">Consultando disponibilidade...</div>';

    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('domain', full);

    try {
        const res = await fetch('', { method: 'POST', body: formData });
        const data = await res.json();
        if (!data.success) {
            resultArea.innerHTML = '<div class="unknown result">' + (data.message || 'Erro ao consultar') + '</div>';
            return;
        }

        const price = (parseFloat(data.price) || 0).toFixed(2);
        let html = '';
        if (data.available === true) {
            html += '<div class="available result"><strong>Disponível!</strong><div class="small">Preço: R$ ' + price + ' / ano</div></div>';
            html += '<div style="margin-top:10px;"><button class="reserve-btn" id="reserveBtn">Reservar domínio</button></div>';
        } else if (data.available === false) {
            html += '<div class="unavailable result"><strong>Indisponível</strong><div class="small">Provávelmente já registrado.</div></div>';
        } else {
            html += '<div class="unknown result"><strong>Indeterminado</strong><div class="small">Não foi possível obter disponibilidade com precisão. ' + (data.note ? data.note : '') + '</div></div>';
        }

        html += '<div class="small" style="margin-top:8px;"><strong>Provedor:</strong> ' + (data.provider || 'nenhum') + ' — ' + (data.note || '') + '</div>';
        resultArea.innerHTML = html;

        // Lidar com reservar (chamar API de reserva)
        if (data.available === true) {
            document.getElementById('reserveBtn').addEventListener('click', async function() {
                this.disabled = true;
                this.textContent = 'Reservando...';
                try {
                    const res2 = await fetch('api/reserve-domain.php', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: new URLSearchParams({ domain: full })
                    });
                    const j = await res2.json();
                    if (j.success) {
                        alert('Domínio reservado por ' + (j.reserved_minutes || 15) + ' minutos. Prossiga para o checkout.');
                        // redirecionar para confirmar pedido / checkout
                        window.location.href = 'planos.php?selected_domain=' + encodeURIComponent(full);
                    } else {
                        alert('Erro ao reservar: ' + (j.message || 'Erro desconhecido'));
                        this.disabled = false;
                        this.textContent = 'Reservar domínio';
                    }
                } catch (err) {
                    alert('Erro ao reservar domínio. Verifique sua conexão.');
                    this.disabled = false;
                    this.textContent = 'Reservar domínio';
                }
            });
        }

    } catch (err) {
        resultArea.innerHTML = '<div class="unknown result">Erro ao consultar disponibilidade.</div>';
    }
});
</script>

</body>
</html>