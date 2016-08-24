<?php

namespace Moneris;

class CustInfo
{
    protected $level3template = [
        'cust_info' => [
            'email',
            'instructions',
            'billing' => [
                'first_name',
                'last_name',
                'company_name',
                'address',
                'city',
                'province',
                'postal_code',
                'country',
                'phone_number',
                'fax',
                'tax1',
                'tax2',
                'tax3',
                'shipping_cost',
            ],
            'shipping' => [
                'first_name',
                'last_name',
                'company_name',
                'address',
                'city',
                'province',
                'postal_code',
                'country',
                'phone_number',
                'fax',
                'tax1',
                'tax2',
                'tax3',
                'shipping_cost',
            ],
            'item' => [
                'name',
                'quantity',
                'product_code',
                'extended_amount',
            ],
        ],
    ];

    protected $level3data;
    protected $email;
    protected $instructions;

    function __construct($custInfo = 0, $billing = 0, $shipping = 0, $items = 0)
    {
        if($custInfo) {
            $this->setCustInfo($custInfo);
        }
    }

    function setCustInfo($custInfo)
    {
        $this->level3data['cust_info'] = array($custInfo);
    }

    function setEmail($email)
    {
        $this->email=$email;
        $this->setCustInfo(array('email' => $email, 'instructions' => $this->instructions));
    }

    function setInstructions($instructions)
    {
        $this->instructions=$instructions;
        $this->setCustInfo(array('email' => $this->email, 'instructions' => $instructions));
    }

    function setShipping($shipping)
    {
        $this->level3data['shipping'] = array($shipping);
    }

    function setBilling($billing)
    {
        $this->level3data['billing'] = array($billing);
    }

    function setItems($items)
    {
        if(! $this->level3data['item']) {
            $this->level3data['item'] = array($items);
        } else {
            $index = count($this->level3data['item']);
            $this->level3data['item'][$index] = $items;
        }
    }

    function toXML()
    {
        $xmlString = $this->toXML_low($this->level3template, "cust_info");
        return $xmlString;
    }

    function toXML_low($template, $txnType)
    {
        $xmlString = "";

        for ($x = 0; $x < count($this->level3data[$txnType]); $x++) {
            if ($x > 0) {
                $xmlString .= "</$txnType><$txnType>";
            }
            $keys = array_keys($template);

            for ($i = 0; $i < count($keys); $i++) {
                $tag = $keys[$i];

                if(is_array($template[$keys[$i]])) {
                    $data = $template[$tag];

                    if(! count($this->level3data[$tag])) {
                        continue;
                    }
                    $beginTag = "<$tag>";
                    $endTag = "</$tag>";

                    $xmlString .= $beginTag;
                    // if (is_array($data)) {
                        $returnString = $this->toXML_low($data,$tag);
                        $xmlString .= $returnString;
                    //}
                    $xmlString .=$endTag;
                } else {
                    $tag = $template[$keys[$i]];
                    $beginTag = "<$tag>";
                    $endTag = "</$tag>";
                    $data = $this->level3data[$txnType][$x][$tag];
                    $xmlString .= $beginTag.$data .$endTag;
                }

            }//end inner for

        }//end outer for

        return $xmlString;
    }
}
