<?php

/**
 * Class responsible for sending a soap via the curl method
 * 
 * @author Gabriel Maia <gabriel_more@hotmail.com>
 */
class curlSoap{

  private $token;
  private $dataToken;
  private $tokenTemporario;
  private $data;

  /**
   * Constructor method responsible for capturing the necessary parameters
   * 
   * @param $token header param
   * @param $dataToken header param
   * @param $tokenTemporario header param
   * @param $data value of soap body
   */
  public function __construct($token, $dataToken, $tokenTemporario, $data)
  {
    $this->token= $token;
    $this->dataToken = $dataToken;
    $this->tokenTemporario = $tokenTemporario;
    $this->data = $data;
  }

  /**
   * This method treats the soap and sets up the connection by sending
   * 
   * @return Array of soap response
   */
  public function sendSoap(){
    $soap_body = '<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:lis="endpoint">
    <soapenv:Header>
        <lis:Metadata>
          <lis:Token>{1}</lis:Token>
          <lis:Data-Token>{2}</lis:Data-Token>
          <lis:Token-Temporario>{3}</lis:Token-Temporario>
        </lis:Metadata>
    </soapenv:Header>
    <soapenv:Body>
        <lis:metodoExemplo>
          <lis:exemplo>{4}</lis:exemplo>
        </lis:metodoExemplo>
    </soapenv:Body>
  </soapenv:Envelope>';
  
    $soap_body = str_replace("{1}", $this->token, $soap_body);
    $soap_body = str_replace("{2}", $this->dataToken, $soap_body);
    $soap_body = str_replace("{3}", $this->tokenTemporario, $soap_body);
    $soap_body = str_replace("{4}", $this->data, $soap_body);

    $headers = array
    ( 
      'Content-Type: text/xml; charset="utf-8"', 
      'Content-Length: '. strlen($soap_body), 
      'Accept: text/xml', 
      'Cache-Control: no-cache', 
      'Pragma: no-cache'
    );
  
    $ch = curl_init(); 
  
    curl_setopt($ch, CURLOPT_URL, 'endpoint'); 
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_body); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  
    $result = curl_exec($ch);
    $plainXML = $this->xmlPlainText( trim($result) );
    $arrayResult = json_decode(json_encode(SimpleXML_Load_String($plainXML, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
  
    return $arrayResult;
  }

  /**
   * This method converts a xml soap into a clean xml
   * 
   * @param $xml variable of containing soap xml
   * @return Returns an xml understandable by php
   */
  private function xmlPlainText($xml)
  {
      $obj = SimpleXML_Load_String($xml);
      if ($obj === FALSE) return $xml;

      $nss = $obj->getNamespaces(TRUE);
      if (empty($nss)) return $xml;

      $nsm = array_keys($nss);
      foreach ($nsm as $key)
      {
          $rgx
          = '#'               
          . '('               
          . '\<'              
          . '/?'              
          . preg_quote($key)  
          . ')'               
          . '('              
          . ':{1}'            
          . ')'               
          . '#'               
          ;
          $rep
          = '$1'          
          . '_'           
          ;
          $xml =  preg_replace($rgx, $rep, $xml);
      }
      return $xml;
  }
}

?>

