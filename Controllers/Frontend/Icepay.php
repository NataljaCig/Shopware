<?php

use Shopware\Plugins\Local\Frontend\IcePay\Models\IcePay as Icepay;
use Shopware\Plugins\Local\Frontend\IcePay\Components\IcePayQuerySender as IcepayQuerySender;

/**
 * IcePay controller
 */
class Shopware_Controllers_Frontend_IcePay extends Enlight_Controller_Action
{
    public $admin;
    public $order;
    public $basket;
    public $session;
    public $db;

    public function init()
    {
        $this->db = Shopware()->Db();
        $this->order = Shopware()->Modules()->Order();
        $this->admin = Shopware()->Modules()->Admin()->sGetUserData();
        $this->basket = Shopware()->Modules()->Basket()->sGetBasket();
        $this->session = Shopware()->Session();
    }


    public function getOrderIdByOrderNumber($orderNumber)
    {
        return $this->db->fetchOne(
            'SELECT id FROM s_order WHERE ordernumber = :orderNumber;',
            array(':orderNumber' => $orderNumber)
        );
    }


    public function successfulPaymentAction() {
        if ($_REQUEST['Status'] == 'OPEN') {
            return $this->redirect('/frontend/icepay/pending');
        }
        $model = new IcePay();
        $methodUrl = IcePay::GET_PAYMENT_URL;

        $queryArray = [
            "Timestamp" => date('Y-m-d\TH:i:s'),
            "PaymentID" => $_REQUEST['PaymentID'],
        ];

        $model->setQueryParams($queryArray);
        $headers = $model->getHeaders($methodUrl);
        $querySender = new IcePayQuerySender($methodUrl, $headers, $queryArray);
        if ($responseArray = $querySender->sendQuery())
        {
            if (isset($responseArray['Status']) == 'OK')
            {
                $order = Shopware()->Modules()->Order()->getOrderById(((int)$responseArray['OrderID'])-10000);
                if ($order) {
                    $orderNumber = $this->saveOrder($responseArray['PaymentID']);
                    $orderId = $this->getOrderIdByOrderNumber($orderNumber);
                    $this->order->setPaymentStatus($orderId, \Shopware\Models\Order\Status::PAYMENT_STATE_COMPLETELY_PAID, true);
                    return $this->redirect('/checkout/finish/sUniqueID/'.$order['temporaryID']);
                } else {
                    return $this->redirect('/');
                }

            }
            else
            {
                echo '<pre>';
                var_dump($responseArray);die();
                return false;   //TODO failed query
            }
        }
        echo '<pre>';
        var_dump($responseArray);die();
    }


    public function failedPaymentAction() {
        if ($_REQUEST['Status'] == 'OPEN') {
            return $this->redirect('/frontend/icepay/pending');
        }
        $failText = Shopware()->Plugins()->Frontend()->IcePay()->getFailText();
        $backToShopUrl = '/';
        $backToShopTitle = 'Back to shop';
        $backToCheckout = 'Back to checkout';
        $backToChangePayment = 'Back to choose payment';
        $this->View()->assign('failText', $failText);
        $this->View()->assign('backToShopUrl', $backToShopUrl);
        $this->View()->assign('backToShopTitle', $backToShopTitle);
        $this->View()->assign('backToCheckout', $backToCheckout);
        $this->View()->assign('backToChangePayment', $backToChangePayment);
    }


    public function pendingAction() {
        $this->saveOrder('');
        $pendingText = Shopware()->Plugins()->Frontend()->IcePay()->getPendingText();
        $backToShopUrl = '/';
        $backToShopTitle = 'Back to shop';
        $backToCheckout = 'Back to checkout';
        $backToChangePayment = 'Back to choose payment';
        $this->View()->assign('pendingText', $pendingText);
        $this->View()->assign('backToShopUrl', $backToShopUrl);
        $this->View()->assign('backToShopTitle', $backToShopTitle);
        $this->View()->assign('backToCheckout', $backToCheckout);
        $this->View()->assign('backToChangePayment', $backToChangePayment);
    }



    public function indexAction() {
        $payments = Shopware()->Models()->createQuery("SELECT p FROM Shopware\CustomModels\IcePayPayments p WHERE p.state = 1 ORDER BY p.position")->getResult();
        $issuers = Shopware()->Models()->createQuery("SELECT i FROM Shopware\CustomModels\IcePayIssuers i WHERE i.state = 1 ORDER BY i.position")->getResult();
        foreach ($payments as $k => $payment) {
            $payments[$k]->issuers = [];
            foreach ($issuers as $issuer) {
                if ($issuer->payment_id == $payment->id) {
                    $payments[$k]->issuers[] = $issuer;
                    continue;
                }
            }
        }
        foreach ($payments as $k => $payment) {
            $payments[$k]->isEmptyIssuers = empty($payment->issuers);
        }
        $this->View()->assign('payments', $payments);
        $this->View()->assign('routeToShippingItemTpl', __DIR__.'/../../Views/frontend/icepay/shipping_item.tpl');
        $this->View()->assign('routeToShippingStepsTpl', __DIR__.'/../../Views/frontend/icepay/steps.tpl');
    }


    public function processAction() {
        if (!empty($_REQUEST)) {
            $this->redirect('/');
        }

        $payment = $_REQUEST['payment'];
        $issuer = $_REQUEST['issuer'];


        $model = new IcePay();

        $methodUrl = IcePay::CHECKOUT_URL;
        $successfulUrl = $model->getSuccessfulUrl();
        $failUrl = $model->getFailUrl();

        $icePayPlugin = Shopware()->Plugins()->Frontend()->IcePay();
        $userId = $this->admin['additional']['user']['id'];

        if ($icePayPlugin->isTestMode() and !in_array($userId, $icePayPlugin->getTestUsersIds())) {
            return $this->redirect('/checkout/confirm');
        }

        $userIp = $_SERVER['REMOTE_ADDR'];

        //TODO Replace on checkout/finish
        $uniqueOrderId = Shopware()->Session()->offsetGet('sessionId');
        $orderId = $model->getOrderID($uniqueOrderId);

        $queryArray = [
            "Timestamp" => date('Y-m-d\TH:i:s'),
            "Amount" => intval($this->basket['Amount'] * 100),
            "Country" => "NL",
            "Currency" => "EUR",
            "Description" => "Order from the web shop",
            "EndUserIP" => $userIp,
            "PaymentMethod" => $payment,
            "Language" => "EN",
            "OrderID" => ($orderId['id'] + 10000),
            "URLCompleted" => $successfulUrl,
            "URLError" => $failUrl
        ];

        $model->setQueryParams($queryArray);
        $headers = $model->getHeaders($methodUrl);

        $querySender = new IcePayQuerySender($methodUrl, $headers, $queryArray);
        if ($responseArray = $querySender->sendQuery())
        {
            if (!isset($responseArray['Message']))
            {
                return $this->redirect($responseArray['PaymentScreenURL']);
            }
            else
            {
                echo '<pre>';
                var_dump($responseArray);die();
                return false;   //TODO failed query
            }
        }
        echo '<pre>';
        var_dump($responseArray);die();
        return false;   //TODO failed query
    }



    public function saveOrder($transactionId = '')
    {
        $sUserData = $this->admin;
        $sBasket = $this->basket;

        $this->order->sUserData = $sUserData;
        $this->order->sComment = isset($this->session['sComment']) ? $this->session['sComment'] : '';
        $this->order->sBasketData = $sBasket;
        $this->order->sAmount = $sBasket['sAmount'];
        $this->order->sAmountWithTax = !empty($sBasket['AmountWithTaxNumeric']) ? $sBasket['AmountWithTaxNumeric'] : $sBasket['AmountNumeric'];
        $this->order->sAmountNet = $sBasket['AmountNetNumeric'];
        $this->order->bookingId = $transactionId;
        $this->order->sShippingcosts = $sBasket['sShippingcosts'];
        $this->order->sShippingcostsNumeric = $sBasket['sShippingcostsWithTax'];
        $this->order->sShippingcostsNumericNet = $sBasket['sShippingcostsNet'];
        $this->order->dispatchId = $this->session['sDispatch'];
        $this->order->sNet = !$sUserData['additional']['charge_vat'];
        $this->order->deviceType = $this->Request()->getDeviceType();

        return $this->order->sSaveOrder();
    }

}
