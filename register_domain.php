<?php
$apiUser = "TiagoBaptista13";
$apiKey = "d528fc44618a47e789db98b20c772872";
$userName = "TiagoBaptista13";
$clientIp = "167.250.106.18";

$domain = "meutestedominiotiago.com"; // domínio de teste
$years = 1;

// Mesmos dados para todos os contatos
$contact = [
  'FirstName' => 'Tiago',
  'LastName' => 'Baptista',
  'Address1' => 'Rua Exemplo, 123',
  'City' => 'São José do Rio Pardo',
  'StateProvince' => 'SP',
  'PostalCode' => '13720000',
  'Country' => 'BR',
  'Phone' => '+55.19999999999',
  'EmailAddress' => 'contato@sitesparaempresas.com'
];

$params = [
  'ApiUser' => $apiUser,
  'ApiKey' => $apiKey,
  'UserName' => $userName,
  'ClientIp' => $clientIp,
  'Command' => 'namecheap.domains.create',
  'DomainName' => $domain,
  'Years' => $years,
];

// Adiciona os quatro tipos de contato
foreach (['Registrant', 'Tech', 'Admin', 'AuxBilling'] as $type) {
  foreach ($contact as $key => $value) {
    $params[$type . $key] = $value;
  }
}

$url = "https://api.sandbox.namecheap.com/xml.response?" . http_build_query($params);

$response = file_get_contents($url);
$xml = simplexml_load_string($response);

if ($xml->Errors->Error) {
  echo "<b>Erro:</b> " . $xml->Errors->Error . "<br>";
} else {
  echo "<b>Domínio registrado com sucesso!</b><br>";
  echo "Ordem Nº: " . $xml->CommandResponse->DomainCreateResult['DomainID'] . "<br>";
  echo "Domínio: " . $xml->CommandResponse->DomainCreateResult['Domain'] . "<br>";
}
?>