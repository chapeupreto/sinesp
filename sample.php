<?php

require_once 'Sinesp.php';

$veiculo = new Sinesp;
$veiculo->proxy('177.54.144.208', '80'); // a consulta vai usar proxy

try {
    $veiculo->buscar('GWW-6471');

    if ($veiculo->existe()) {
        print_r($veiculo->dados());
        echo 'O ano do veiculo eh ' , $veiculo->anoModelo, PHP_EOL;
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
