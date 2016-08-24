<?php

namespace Moneris;

class AchInfo
{
    protected $params;
    protected $achTemplate = [
        'sec',
        'cust_first_name',
        'cust_last_name',
        'cust_address1',
        'cust_address2',
        'cust_city',
        'cust_state',
        'cust_zip',
        'routing_num',
        'account_num',
        'check_num',
        'account_type',
        'micr'
    ];

    function __construct($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach ($this->achTemplate as $tag) {
            $xmlString .= "<$tag>" . $this->params[$tag] . "</$tag>";
        }

        return "<ach_info>$xmlString</ach_info>";
    }
}
