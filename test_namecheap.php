<?php
$apiUser = "TiagoBaptista13";
$apiKey = "d528fc44618a47e789db98b20c772872";
$userName = "TiagoBaptista13";
$clientIp = "167.250.106.18";

$domain = "meutestedominiotiago.com";

$url = "https://api.sandbox.namecheap.com/xml.response?" . http_build_query([
  'ApiUser' => $apiUser,
  'ApiKey' => $apiKey,
  'UserName' => $userName,
  'ClientIp' => $clientIp,
  'Command' => 'namecheap.domains.check',
  'DomainList' => $domain
]);

$response = file_get_contents($url);
$xml = simplexml_load_string($response);

if ($xml === false) {
  echo "Erro ao conectar à API.";
  exit;
}

$result = $xml->CommandResponse->DomainCheckResult;
echo "Domínio: " . $result['Domain'] . "<br>";
echo "Disponível: " . ($result['Available'] == 'true' ? 'Sim' : 'Não');
?>
