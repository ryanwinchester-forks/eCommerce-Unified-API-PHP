<?php

namespace Moneris;

class Recur
{
    protected $params;
    protected $recurTemplate = ['recur_unit', 'start_now', 'start_date', 'num_recurs', 'period', 'recur_amount'];

    function __construct($params)
    {
        $this->params = $params;
        $this->params['period'] = !empty($params['period']) ? $params['period'] : 1;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->recurTemplate as $tag) {
            $xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
        }

        return "<recur>$xmlString</recur>";
    }

}
