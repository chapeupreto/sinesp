<?php																									
																									
namespace Sinesp;																									
																									
class Sinesp																									
{																									
	private $secret = '#8.1.0#0KnlVSWHxOih3zKXBWlo';																									
	private $url = 'https://cidadao.sinesp.gov.br/sinesp-cidadao/mobile/consultar-placa/v5';																									
	private $proxy = '';																									
																										
	private $placa = '';																									
	private $response = '';																									
	private $dados = [];																									
																										
	private $fToken = '';																									
	private $firebaseToken = '';																									
																										
	/**																									
	* Time (in seconds) to wait for a response																									
	* @var int																									
	*/																									
	private $timeout = 10;																									
																										
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
	* @param int $seconds How much seconds to wait																									
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
																										
        $headers = array(																									
            "Content-type: application/x-www-form-urlencoded; charset=UTF-8",																									
            "Accept: text/plain, */*; q=0.01",																									
            "Cache-Control: no-cache",																									
            "Pragma: no-cache",																									
            "Content-length: " . strlen($xml),																									
            "User-Agent: SinespCidadao / 3.0.2.1 CFNetwork / 758.2.8 Darwin / 15.0.0",																									
            "Authorization: Token " . $this->fToken . ":" . end($this->firebaseToken)																									
	    );																									
																										
        $ch = curl_init();																									
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);																									
        curl_setopt($ch, CURLOPT_URL, $this->url);																									
                                                                                                            
        if ($this->proxy) {																									
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);																									
        }																									
                                                                                                            
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);																									
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);																									
                                                                                                            
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);																									
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);																									
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);																									
        curl_setopt($ch, CURLOPT_POST, true);																									
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
    																									
	private function getFToken() {																									
        $firebaseAuth = 'li69ee1KY52:APA91bEtwOpw_NZsSeBgdW5fmQsBf0CgDmZ0txJ5dAuyRQuW6ozSO2XpNuCYJhfOUrrbQACCIJ4dgsGQ6fqD4GJB19cE2vHqcvOJueW6xl6Vd4YgjWQBh91Xin82JvW_pBLHOw6Cvo9j';																									
        $this->firebaseToken = explode(':', $firebaseAuth);																									
        $this->fToken = (strlen($this->firebaseToken[0]) == 11) ? $this->firebaseToken[0] : $this->firebaseToken[1];																									
        return $this->fToken;																									
	}																									
																										
	private function token()																									
	{																									
	    return hash_hmac('sha1', $this->placa, $this->placa . $this->secret);																									
	}																									
																										
	private function xml()																									
	{																									    																							
        $xml = <<<EOX
<v:Envelope xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns:d="http://www.w3.org/2001/XMLSchema" xmlns:c="http://schemas.xmlsoap.org/soap/encoding/" xmlns:v="http://schemas.xmlsoap.org/soap/envelope/">																									
<v:Header>																									
<b>motorola</b>																									
<c>ANDROID</c>																									
<d>8.1.0</d>																									
<e>4.7.4</e>																									
<f>10.0.0.1</f>																									
<g>%s</g>																									
<h>0</h>																									
<i>0</i>																									
<k />																									
<l>%s</l>																									
<m>8797e74f0d6eb7b1ff3dc114d4aa12d3</m>																									
<n>%s</n></v:Header>																									
<v:Body>																									
<n0:getStatus xmlns:n0="http://soap.ws.placa.service.sinesp.serpro.gov.br/">																									
<a>%s</a> </n0:getStatus>																									
</v:Body>																									
</v:Envelope>
EOX;
	    return sprintf($xml, $this->token(), date("Y-m-d H:i:s"), $this->getFToken(), $this->placa);																									
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