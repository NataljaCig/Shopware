<?php

namespace Shopware\Plugins\Local\Frontend\IcePay\Components;

/**
 * Created by PhpStorm.
 * User: future
 * Date: 02/08/16
 * Time: 22:41
 */
class IcePayQuerySender
{
    public $methodUrl;
    public $headers;
    public $queryArray;


    public function __construct($methodUrl, $headers, $queryArray) {
        $this->methodUrl = $methodUrl;
        $this->headers = $headers;
        $this->queryArray = $queryArray;
    }


    public function sendQuery() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->methodUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->queryArray));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = @curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response,true);

        return $responseData;
    }
}