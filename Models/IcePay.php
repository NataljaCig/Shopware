<?php

namespace Shopware\Plugins\Local\Frontend\IcePay\Models;


class IcePay
{
    const CHECKOUT_URL = 'https://connect.icepay.com/webservice/api/v1/payment/checkout/';
    const GET_PAYMENT_URL = 'https://connect.icepay.com/webservice/api/v1/payment/GetPayment/';
    const GET_PAYMENT_METHODS = 'https://connect.icepay.com/webservice/api/v1/payment/GetMyPaymentMethods/';

    public $_params;
    private $db;

    public function __construct() {
        $this->db = Shopware()->Db();
    }

    public function getCheckSum($methodUrl) {
        $plugin = Shopware()->Plugins()->Frontend()->IcePay();
        $merchantId = $plugin->getMerchantId();
        $secret = $plugin->getSecretCode();
        $str = $methodUrl.'POST'.$merchantId.$secret.json_encode($this->getQueryParams());
        $checkSum = hash('sha256', $str);
        return $checkSum;
    }


    public function getSuccessfulUrl() {
        return 'http://'.Shopware()->Config()->Host.self::SUCCESS_URL;
    }


    public function getFailUrl() {
        return 'http://'.Shopware()->Config()->Host.self::FAIL_URL;
    }


    public function getQueryParams()
    {
        return $this->_params;
    }


    public function setQueryParams(array $params)
    {
        $this->_params = $params;
    }

    public function getHeaders($methodUrl)
    {
        return [
            'MerchantID: '.intval(Shopware()->Plugins()->Frontend()->IcePay()->getMerchantId()),
            'Checksum: '.$this->getCheckSum($methodUrl),
            'Content-Type: application/json'
        ];
    }

    
    public function assignTransactionId($transactionId, $id) {
        return $this->db->executeUpdate(
            "UPDATE s_order SET transactionID = {$transactionId} WHERE id={$id}"
        );
    }
    

    public function getOrderID($uniqueID) {
        $sql = <<<EOT
SELECT
    `o`.`id`
FROM
    `s_order` as `o`
WHERE
    `o`.`temporaryID` = :temporaryID
EOT;

        $row = $this->db->fetchRow($sql, ['temporaryID' => $uniqueID]);

        return $row;

    }
}