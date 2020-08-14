<?php

/**
 * Class responsible for creating a client for sending soap protocol
 * 
 * @author Gabriel Maia <gabriel_more@hotmail.com>
 */
class sendSoap
{
  private $client;
  private $endpoint;
  private $namespace;

  /**
   * Class constructor, receives two soapClient initialization parameters
   * 
   * @param $endpoint soap service connection url
   * @param $namespace namespace used by soap 
   */
  public function __construct($endpoint, $namespace)
  {
    $this->namespace = $namespace;
    $this->endpoint = $endpoint;
    $this->client = new SoapClient($this->endpoint, array(
      'location' => str_replace("?wsld", "", $this->endpoint),
      'keep_alive' => false,
      "stream_context" => stream_context_create([
        'ssl' => [
          'crypto_method' =>  STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
        ]
      ]),
      'trace' => 1,
    ));
  }

  /**
   * This method receives the parameters that will be sent in the header
   * 
   * @param $params header values
   * @param $parant element to which the values ​​will be inserted
   */
  public function addHeaders($params, $parent){
    $header = new SoapHeader($this->namespace, $parent, $params, false);
    $this->client->__setSoapHeaders($header);
  }

  /**
   * This method makes the service call by passing the method name and its values ​​as a parameter
   * 
   * @param $operation name of the method that will receive the data
   * @param $params values ​​that will be inserted into the body of the service
   * 
   * @return an array of service information
   */
  public function sendSoap($operation, $params){
    $result = $this->client->__soapCall($operation, $params);

    return $result;
  }
}
