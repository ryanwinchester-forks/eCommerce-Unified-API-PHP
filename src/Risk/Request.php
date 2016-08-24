<?php

namespace Moneris\Risk;

use Moneris\Globals;

class Request
{
    protected $txnTypes = [
        'session_query'   => ['order_id', 'session_id', 'service_type', 'event_type'],
        'attribute_query' => ['order_id', 'policy_id', 'service_type'],
        'assert'          => [
            'orig_order_id',
            'activities_description',
            'impact_description',
            'confidence_description'
        ]
    ];

    protected $txnArray;
    protected $procCountryCode = "";
    protected $testMode = "";

    function __construct($txn)
    {
        $this->txnArray = (array) $txn;
    }

    function setProcCountryCode($countryCode)
    {
        $this->procCountryCode = ((strcmp(strtolower($countryCode), "us") >= 0) ? "_US" : "");
    }

    function setTestMode($state)
    {
        if ($state === true) {
            $this->testMode = "_TEST";
        } else {
            $this->testMode = "";
        }
    }

    function getURL()
    {
        $g = new Globals();
        $gArray = $g->getGlobals();

        //$txnType = $this->getTransactionType();

        $hostId = "MONERIS" . $this->procCountryCode . $this->testMode . "_HOST";
        $fileId = "MONERIS" . $this->procCountryCode . "_FILE";

        $url = $gArray['MONERIS_PROTOCOL'] . "://" .
            $gArray[$hostId] . ":" .
            $gArray['MONERIS_PORT'] .
            $gArray[$fileId];

        echo "PostURL: " . $url;

        return $url;
    }

    function toXML()
    {
        $tmpTxnArray = $this->txnArray;
        $xmlString = "";

        $txnArrayLen = count($tmpTxnArray); //total number of transactions
        for ($x = 0; $x < $txnArrayLen; $x++) {
            $txnObj = $tmpTxnArray[$x];
            $txn = $txnObj->getTransaction();

            $txnType = array_shift($txn);
            $tmpTxnTypes = $this->txnTypes;
            $txnTypeArray = $tmpTxnTypes[$txnType];
            $txnTypeArrayLen = count($txnTypeArray); //length of a specific txn type

            $txnXMLString = "";
            for ($i = 0; $i < $txnTypeArrayLen; $i++) {
                $txnXMLString .= "<$txnTypeArray[$i]>"   //begin tag
                    . $txn[$txnTypeArray[$i]] // data
                    . "</$txnTypeArray[$i]>"; //end tag
            }

            $txnXMLString = "<$txnType>$txnXMLString";

            $sessionQuery = $txnObj->getSessionAccountInfo();

            if ($sessionQuery != null) {
                $txnXMLString .= $sessionQuery->toXML();
            }

            $attributeQuery = $txnObj->getAttributeAccountInfo();

            if ($attributeQuery != null) {
                $txnXMLString .= $attributeQuery->toXML();
            }

            $txnXMLString .= "</$txnType>";

            $xmlString .= $txnXMLString;

            return $xmlString;
        }

        return $xmlString;
    }
}
