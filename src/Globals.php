<?php

namespace Moneris;

class Globals
{
    static $globals = [
        'MONERIS_PROTOCOL'     => 'https',
        'MONERIS_HOST'         => 'www3.moneris.com',
        'MONERIS_TEST_HOST'    => 'esqa.moneris.com',
        'MONERIS_US_HOST'      => 'esplus.moneris.com',
        'MONERIS_US_TEST_HOST' => 'esplusqa.moneris.com',
        'MONERIS_PORT'         => '443',
        'MONERIS_FILE'         => '/gateway2/servlet/MpgRequest',
        'MONERIS_US_FILE'      => '/gateway_us/servlet/MpgRequest',
        'MONERIS_MPI_FILE'     => '/mpi/servlet/MpiServlet',
        'MONERIS_US_MPI_FILE'  => '/mpi/servlet/MpiServlet',
        'API_VERSION'          => 'PHP NA - 1.0.3',
        'CLIENT_TIMEOUT'       => '60'
    ];

    static function get()
    {
        return static::$globals;
    }

    function getGlobals()
    {
        return static::$globals;
    }

}
