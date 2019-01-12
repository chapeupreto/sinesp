<?php

namespace Sinesp;

class Sinesp
{
    private $secret = '#8.1.0#g8LzUadkEHs7mbRqbX5l';
    private $url = 'https://cidadao.sinesp.gov.br/sinesp-cidadao/mobile/consultar-placa/v4';
    private $proxy = '';

    private $placa = '';
    private $response = '';
    private $dados = [];

    /**
     * Time (in seconds) to wait for a response
     * @var int
     */
    private $timeout = 0;

    public function buscar($placa, array $proxy = [])
    {
        if ($proxy) {
            $this->proxy($proxy['ip'], $proxy['porta']);
        }

        $this->setUp($placa);
        $this->exec();

        return $this;
    }

    public function dados()
    {
        return $this->dados;
    }

    public function proxy($ip, $porta)
    {
        $this->proxy = $ip . ':' . $porta;
    }

    /**
     * Set a timeout for request(s) that will be made
     * @param  int  $seconds How much seconds to wait
     * @return self
     */
    public function timeout($seconds)
    {
        $this->timeout = $seconds;

        return $this;
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

        $headers = [
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Content-length: ' . strlen($xml),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);

        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $this->response = curl_exec($ch);

        curl_close($ch);
    }

    private function tratarResposta()
    {
        if (!$this->response) {
            throw new \Exception('O servidor retornou nenhuma resposta!');
        }

        $response = str_ireplace(['soap:', 'ns2:'], '', $this->response);

        $this->dados = (array) simplexml_load_string($response)->Body->getStatusResponse->return;
    }

    private function verificarRequisitos()
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('Incapaz de processar. PHP requer biblioteca cURL');
        }

        if (!function_exists('simplexml_load_string')) {
            throw new \Exception('Incapaz de processar. PHP requer biblioteca libxml');
        }

        return;
    }

    private function setUp($placa)
    {
        if (!$this->validar($placa)) {
            throw new \Exception('Placa do veiculo nao especificada ou em formato invalido!');
        }

        $this->placa = $this->ajustar($placa);
    }

    private function token()
    {
        return hash_hmac('sha1', $this->placa, $this->placa . $this->secret);
    }

    private function xml()
    {
        $xml = <<<EOX
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<v:Envelope xmlns:v="http://schemas.xmlsoap.org/soap/envelope/">
<v:Header>
<b>samsung GT-I9192</b>
<c>ANDROID</c>
<d>8.1.0</d>
<i>%s</i>
<e>4.1.5</e>
<f>10.0.0.1</f>
<g>%s</g>
<k></k>
<h>%s</h>
<l>%s</l>
<m>8797e74f0d6eb7b1ff3dc114d4aa12d3</m>
</v:Header>
<v:Body>
<n0:getStatus xmlns:n0="http://soap.ws.placa.service.sinesp.serpro.gov.br/">
<a>%s</a>
</n0:getStatus>
</v:Body>
</v:Envelope>
EOX;

        return sprintf($xml, $this->latitude(), $this->token(), $this->longitude(), strftime('%Y-%m-%d %H:%M:%S'), $this->placa);
    }

    private function validar($placa)
    {
        return preg_match('/^[a-z]{3}-?\d[a-z0-9]{2}\d$/i', trim($placa));
    }

    private function ajustar($placa)
    {
        return str_replace('-', '', trim($placa));
    }

    private function latitude()
    {
        return '-38.5' . rand(100000, 999999);
    }

    private function longitude()
    {
        return '-3.7' . rand(100000, 999999);
    }
}
