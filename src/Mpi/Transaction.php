<?php

namespace Moneris\Mpi;

class Transaction
{
    protected $transaction;

    function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    function getTransaction()
    {
        return $this->transaction;
    }
}
