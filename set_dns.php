<?php
$apiUser = "TiagoBaptista13";
$apiKey = "d528fc44618a47e789db98b20c772872";
$userName = "TiagoBaptista13";
$clientIp = "167.250.106.18";

$domain = "meutestedominiotiago.com"; // mesmo domínio do teste

// DNS da Hostinger (substitua pelos que aparecem no seu painel)
$nameservers = "ns1.dns-parking.com,ns2.dns-parking.com";

$url = "https://api.sandbox.namecheap.com/xml.response?" . http_build_query([
  'ApiUser' => $apiUser,
  'ApiKey' => $apiKey,
  'UserName' => $userName,
  'ClientIp' => $clientIp,
  'Command' => 'namecheap.domains.dns.setCustom',
  'SLD' => explode('.', $domain)[0],
  'TLD' => explode('.', $domain)[1],
  'Nameservers' => $nameservers
]);

$response = file_get_contents($url);
$xml = simplexml_load_string($response);

if ($xml->Errors->Error) {
  echo "<b>Erro:</b> " . $xml->Errors->Error . "<br>";
} else {
  echo "<b>DNS configurado com sucesso!</b><br>";
  echo "Nameservers: " . $nameservers . "<br>";
}
?>