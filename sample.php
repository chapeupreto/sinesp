<?php

require_once 'Sinesp.php';

$veiculo = new Sinesp;

try {
    $veiculo->buscar('GWW-6471');

    if ($veiculo->existe()) {
        print_r($veiculo->dados());
        echo 'O ano do veiculo eh ' , $veiculo->anoModelo, PHP_EOL;
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
