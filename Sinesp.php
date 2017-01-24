<?php

class Sinesp
{
    private $url = 'http://sinespcidadao.sinesp.gov.br/sinesp-cidadao/mobile/consultar-placa';
    private $placa = '';
    private $secret = 'TRwf1iBwvCoSboSscGne';

    private $response = '';
    private $dados = [];

    public function buscar($placa)
    {
        $this->setUp($placa);
        $this->exec();
    }

    public function dados()
    {
        return $this->dados;
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->dados) ? $this->dados[$name] : '';
    }

    public function existe()
    {
        return array_key_exists('codigoRetorno', $this->dados) && $this->dados['codigoRetorno'] != '3';
    }

    private function exec()
    {
        $this->verificarRequisitos();
        $this->obterResposta();
        $this->tratarResposta();
    }

    private function obterResposta()
    {
        $xml = $this->xml();

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: ".strlen($xml),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $this->response = curl_exec($ch);

        curl_close($ch);
    }

    private function tratarResposta()
    {
        $response = str_ireplace(['soap:', 'ns2:'], '', $this->response);

        $this->dados = (array) simplexml_load_string($response)->Body->getStatusResponse->return;
    }

    private function verificarRequisitos()
    {
        if (! function_exists('curl_init')) {
            throw new \Exception('Incapaz de processar. PHP requer biblioteca cURL');
        }

        if (! function_exists('simplexml_load_string')) {
            throw new \Exception('Incapaz de processar. PHP requer biblioteca libxml');
        }

        return;
    }

    private function setUp($placa)
    {
        $placa = $this->ajustar($placa);

        if (! $this->validar($placa)) {
            throw new \Exception('Placa do veiculo nao especificada ou em formato invalido!');
        }

        $this->placa = $placa;
    }

    private function token()
    {
        return hash_hmac('sha1', $this->placa, $this->placa . $this->secret);
    }

    private function xml()
    {
        $xml=<<<EOX
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<soap:Header>
<b>samsung GT-I9192</b>
<c>ANDROID</c>
<d>4.4.2</d>
<g>%s</g>
<l>%s</l>
<m>8797e74f0d6eb7b1ff3dc114d4aa12d3</m>
</soap:Header>
<soap:Body>
<webs:getStatus xmlns:webs="http://soap.ws.placa.service.sinesp.serpro.gov.br/">
<a>%s</a>
</webs:getStatus>
</soap:Body>
</soap:Envelope>
EOX;
        return sprintf($xml, $this->token(), strftime('%Y-%m-%d %T'), $this->placa);
    }

    private function validar($placa)
    {
        return preg_match('/^[a-zA-Z]{3}-?\d{4}$/i', $placa);
    }

    private function ajustar($placa)
    {
        return str_replace(['-', ' '], '', $placa);
    }
}
