<?php

namespace Moneris\Risk;

class Transaction
{
    protected $transaction;
    protected $attributeAccountInfo = null;
    protected $sessionAccountInfo = null;

    function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    function getTransaction()
    {
        return $this->transaction;
    }

    function getAttributeAccountInfo()
    {
        return $this->attributeAccountInfo;
    }

    function setAttributeAccountInfo($attributeAccountInfo)
    {
        $this->attributeAccountInfo = $attributeAccountInfo;
    }

    function getSessionAccountInfo()
    {
        return $this->sessionAccountInfo;
    }

    function setSessionAccountInfo($sessionAccountInfo)
    {
        $this->sessionAccountInfo = $sessionAccountInfo;
    }
}
