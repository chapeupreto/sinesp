<?php

require 'vendor/autoload.php';

use Sinesp\Sinesp;

$veiculo = new Sinesp;

// descomente a linha abaixo para que a consulta utilize proxy. Proxy gratuitos em: https://free-proxy-list.net/
// $veiculo->proxy('177.54.144.208', '80');

try {
    $veiculo->buscar('GWW-6471');

    if ($veiculo->existe()) {
        print_r($veiculo->dados());
        echo 'O ano do veiculo eh ' , $veiculo->anoModelo, PHP_EOL;
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
