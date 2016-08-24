<?php

namespace Moneris;

class Transaction
{
    protected $transaction;
    protected $custInfo = null;
    protected $recur = null;
    protected $cvd = null;
    protected $avs = null;
    protected $convFee = null;
    protected $attributeAccountInfo = null;
    protected $ach = null;
    protected $expDate = null;
    protected $sessionAccountInfo = null;

    function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    function getCustInfo()
    {
        return $this->custInfo;
    }

    function setCustInfo($custInfo)
    {
        $this->custInfo = $custInfo;
        array_push($this->transaction, $custInfo);
    }

    function getRecur()
    {
        return $this->recur;
    }

    function setRecur($recur)
    {
        $this->recur = $recur;
    }

    function getTransaction()
    {
        return $this->transaction;
    }

    function getCvdInfo()
    {
        return $this->cvd;
    }

    function setCvdInfo($cvd)
    {
        $this->cvd = $cvd;
    }

    function getAvsInfo()
    {
        return $this->avs;
    }

    function setAvsInfo($avs)
    {
        $this->avs = $avs;
    }

    function getAchInfo()
    {
        return $this->ach;
    }

    function setAchInfo($ach)
    {
        $this->ach = $ach;
    }

    function setConvFeeInfo($convFee)
    {
        $this->convFee = $convFee;
    }

    function getConvFeeInfo()
    {
        return $this->convFee;
    }

    function setExpiryDate($expDate)
    {
        $this->expDate = $expDate;
    }

    function getExpiryDate()
    {
        return $this->expDate;
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
