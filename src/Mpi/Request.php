<?php

namespace Moneris\Mpi;

use Moneris\Globals;

class Request
{
    protected $transactionTypes = [
        'txn' => [
            'xid',
            'amount',
            'pan',
            'expdate',
            'MD',
            'merchantUrl',
            'accept',
            'userAgent',
            'currency',
            'recurFreq',
            'recurEnd',
            'install'
        ],
        'acs' => ['PaRes', 'MD']
    ];

    protected $transactionArray;
    protected $procCountryCode = "";
    protected $testMode = "";

    function __construct($transaction)
    {
        if (is_array($transaction)) {
            $this->transactionArray = $transaction;
        } else {
            $temp[0] = $transaction;
            $this->transactionArray = $temp;
        }
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

        //$transactionType = $this->getTransactionType();

        $hostId = "MONERIS" . $this->procCountryCode . $this->testMode . "_HOST";
        $fileId = "MONERIS" . $this->procCountryCode . "_MPI_FILE";

        $url = $gArray['MONERIS_PROTOCOL'] . "://" .
            $gArray[$hostId] . ":" .
            $gArray['MONERIS_PORT'] .
            $gArray[$fileId];

        echo "PostURL: " . $url;

        return $url;
    }

    function toXML()
    {
        $tmpTransactionArray = $this->transactionArray;
        $transactionArrayLen = count($tmpTransactionArray); //total number of transactions

        for ($x = 0; $x < $transactionArrayLen; $x++) {
            $transactionObj = $tmpTransactionArray[$x];
            $transaction = $transactionObj->getTransaction();

            $transactionType = array_shift($transaction);
            $tmpTransactionTypes = $this->transactionTypes;
            $transactionTypeArray = $tmpTransactionTypes[$transactionType];
            $transactionTypeArrayLen = count($transactionTypeArray); //length of a specific transaction type

            $transactionXMLString = "";

            for ($i = 0; $i < $transactionTypeArrayLen; $i++) {
                $transactionXMLString .= "<$transactionTypeArray[$i]>"   //begin tag
                    . $transaction[$transactionTypeArray[$i]] // data
                    . "</$transactionTypeArray[$i]>"; //end tag
            }

            $transactionXMLString = "<$transactionType>$transactionXMLString";

            $transactionXMLString .= "</$transactionType>";

            $xmlString .= $transactionXMLString;
        }

        return $xmlString;
    }
}
