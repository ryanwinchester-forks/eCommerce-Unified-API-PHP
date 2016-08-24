<?php

namespace Moneris;

class Client
{
    protected $api_token;
    protected $store_id;
    protected $request;
    protected $response;
    protected $txnType;
    protected $isMPI;
    protected $xmlString = "";

    function __construct($store_id, $api_token, Request $request)
    {
        $this->store_id = $store_id;
        $this->api_token = $api_token;
        $this->request = $request;
        $this->isMPI = $request->getIsMPI();

        $post= new Post($this->request->getURL(), $this->toXML());
        $response = $post->getResponse();

        if (!$response) {
            $response="<?xml version=\"1.0\"?><response><receipt>".
                "<ReceiptId>Global Error Receipt</ReceiptId>".
                "<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
                "<AuthCode>null</AuthCode><TransTime>null</TransTime>".
                "<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
                "<Message>Global Error Receipt</Message><TransAmount>null</TransAmount>".
                "<CardType>null</CardType>".
                "<TransID>null</TransID><TimedOut>null</TimedOut>".
                "<CorporateCard>false</CorporateCard><MessageId>null</MessageId>".
                "</receipt></response>";
        }

        $this->response = new Response($response);
    }

    function getResponse()
    {
        return $this->response;

    }

    function toXML( )
    {
        $reqXMLString = $this->request->toXML();

        if ($this->isMPI === true) {
            $this->xmlString .="<?xml version=\"1.0\"?>".
                "<MpiRequest>".
                "<store_id>$this->store_id</store_id>".
                "<api_token>$this->api_token</api_token>".
                $reqXMLString.
                "</MpiRequest>";
        } else {
            $this->xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
                "<request>".
                "<store_id>$this->store_id</store_id>".
                "<api_token>$this->api_token</api_token>".
                $reqXMLString.
                "</request>";
        }

        return ($this->xmlString);
    }

}
