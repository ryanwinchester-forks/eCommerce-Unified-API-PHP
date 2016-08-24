<?php

namespace Moneris;

class Post
{
    protected $url;
    protected $dataToSend;
    protected $clientTimeOut;
    protected $apiVersion;
    protected $debug;

    function __construct($url, $dataToSend, $debug = false)
    {
        $this->url = $url;
        $this->dataToSend = $dataToSend;
        $this->debug = $debug;
    }

    function send()
    {
        if ($this->debug) {
            echo "DataToSend= ".$this->dataToSend;
            echo "\n\nPostURL= ".$this->url;
        }

        $config = Globals::$globals;
        $clientTimeOut = $config['CLIENT_TIMEOUT'];
        $apiVersion = $config['API_VERSION'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->dataToSend);
        curl_setopt($ch, CURLOPT_TIMEOUT, $clientTimeOut);
        curl_setopt($ch, CURLOPT_USERAGENT, $apiVersion);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        //curl_setopt($ch, CURLOPT_CAINFO, "PATH_TO_CA_BUNDLE");

        $response = curl_exec ($ch);

        curl_close ($ch);

        if ($this->debug) {
            echo "\n\nRESPONSE= $this->response\n";
        }

        return $response;
    }

    function getResponse()
    {
        return $this->send();
    }
}
