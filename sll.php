<?php
// Caminho absoluto para o cacert.pem
$cacert = 'C:/wamp64/bin/php/php8.3.14/extras/ssl/cacert.pem';

// URL de teste (pode ser Mercado Pago ou outro HTTPS)
$url = 'google.com'; // apenas como teste

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_CAINFO, $cacert);

// Executa a requisição
$response = curl_exec($ch);

// Verifica erros
if(curl_errno($ch)) {
    echo "Erro cURL: " . curl_error($ch);
} else {
    echo "Conexão SSL OK!\n";
    echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
    echo "Resposta:\n";
    echo $response;
}

curl_close($ch);
?>
