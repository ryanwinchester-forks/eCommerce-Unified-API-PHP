<?php

namespace Moneris\Risk;

use Moneris\Post;

class Client
{
    protected $api_token;
    protected $store_id;
    protected $request;
    protected $response;

    /**
     * Client constructor.
     * @param $store_id
     * @param $api_token
     * @param Request $request
     */
    function __construct($store_id, $api_token, Request $request)
    {
        $this->store_id = $store_id;
        $this->api_token = $api_token;
        $this->request = $request;

        $post = new Post($this->request->getURL(), $this->toXML());
        $response = $post->getResponse();

        if (! $response) {
            $response = "<?xml version=\"1.0\"?><response><receipt>" .
                "<ReceiptId>Global Error Receipt</ReceiptId>" .
                "<ResponseCode>null</ResponseCode>" .
                "<AuthCode>null</AuthCode><TransTime>null</TransTime>" .
                "<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>" .
                "<Message>null</Message><TransAmount>null</TransAmount>" .
                "<CardType>null</CardType>" .
                "<TransID>null</TransID><TimedOut>null</TimedOut>" .
                "</receipt></response>";
        }

        //print "Got a xml response of: \n$response\n";
        $this->response = new Response($response);
    }

    function getResponse()
    {
        return $this->response;

    }

    function toXML()
    {
        return "<?xml version=\"1.0\"?>" .
            "<request>" .
            "<store_id>$this->store_id</store_id>" .
            "<api_token>$this->api_token</api_token>" .
            "<risk>" .
            $this->request->toXML() .
            "</risk>" .
            "</request>";
    }
}
