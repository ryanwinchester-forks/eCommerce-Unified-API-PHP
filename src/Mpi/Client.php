<?php

namespace Moneris\Mpi;

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
            $response = "<?xml version=\"1.0\"?>" .
                "<MpiResponse>" .
                "<type>null</type>" .
                "<success>false</success>" .
                "<message>null</message>" .
                "<PaReq>null</PaReq>" .
                "<TermUrl>null</TermUrl>" .
                "<MD>null</MD>" .
                "<ACSUrl>null</ACSUrl>" .
                "<cavv>null</cavv>" .
                "<PAResVerified>null</PAResVerified>" .
                "</MpiResponse>";
        }

        $this->response = new Response($response);
    }

    function getResponse()
    {
        return $this->response;
    }

    function toXML()
    {
        return "<?xml version=\"1.0\"?>" .
            "<MpiRequest>" .
            "<store_id>$this->store_id</store_id>" .
            "<api_token>$this->api_token</api_token>" .
            $this->request->toXML() .
            "</MpiRequest>";
    }
}