<?php

namespace Moneris;

class PostStatus
{
    protected $api_token;
    protected $store_id;
    protected $status;
    protected $request;
    protected $response;

    function __construct($store_id, $api_token, $status, Request $request)
    {
        $this->store_id = $store_id;
        $this->api_token = $api_token;
        $this->status = $status;
        $this->request = $request;

        $post = new Post($this->request->getURL(), $this->toXML());
        $response = $post->getResponse();

        if (! $response) {
            $response = "<?xml version=\"1.0\"?><response><receipt>" .
                "<ReceiptId>Global Error Receipt</ReceiptId>" .
                "<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>" .
                "<AuthCode>null</AuthCode><TransTime>null</TransTime>" .
                "<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>" .
                "<Message>Global Error Receipt</Message><TransAmount>null</TransAmount>" .
                "<CardType>null</CardType>" .
                "<TransID>null</TransID><TimedOut>null</TimedOut>" .
                "<CorporateCard>false</CorporateCard><MessageId>null</MessageId>" .
                "</receipt></response>";
        }

        $this->response = new Response($response);
    }

    function getResponse()
    {
        return $this->response;
    }

    function toXML()
    {
        return "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>" .
            "<request>" .
            "<store_id>$this->store_id</store_id>" .
            "<api_token>$this->api_token</api_token>" .
            "<status_check>$this->status</status_check>" .
            $this->request->toXML() .
            "</request>";
    }
}
