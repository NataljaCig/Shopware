<?php

require_once __DIR__ . '/Components/CSRFWhitelistAware.php';

use Shopware\Plugins\Local\Frontend\IcePay\Models\IcePay as Icepay;

class Shopware_Plugins_Frontend_IcePay_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function getVersion()
    {
        return '1.0.0';
    }

    public function getLabel()
    {
        return 'IcePay plugin';
    }

    public function afterInit()
    {
        $this->registerCustomModels();
    }

    public function install()
    {
        $this->registerController('frontend', 'icepay');
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend',
            'onFrontendPostDispatch'
        );

        $this->createConfig();

        $this->registerBackendController();
        $this->createMenu();
        return array(
            'success' => true,
            'invalidateCache' => array('backend')
        );
    }


    public function createMenu()
    {
        $this->createMenuItem(array(
            'label' => 'IcePay payments',
            'onclick' => 'createSimpleModule("IcePayBackend", { "title": "IcePay module" })',
            'class' => 'sprite-star',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy(['label' => 'Einstellungen'])
        ));
    }


    public function registerBackendController()
    {
        $this->registerController(
            'Backend',
            'IcePayBackend'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_IcePayBackend',
            'onGetBackendController'
        );
    }


    public function onGetBackendController()
    {
        $this->get('template')->addTemplateDir($this->Path() . 'Views/');

        return $this->Path() . 'Controllers/Backend/IcePayBackend.php';
    }


    private function createConfig()
    {
        $this->Form()->setElement('text', 'merchant_id',  array('label' => 'Merchant ID', 'value' => ''));
        $this->Form()->setElement('text', 'secret-code',  array('label' => 'Secret code', 'value' => ''));

        $this->Form()->setElement('boolean', 'test-mode', array(
            'value' => false,
            'label' => 'Test Mode (has access only for selected Users below)'
        ));

        $model = new IcePay();

        $this->Form()->setElement('text', 'success-url', array(
            'value' => $this->getSuccessUrl() ?: $model->getSuccessfulUrl(),
            'label' => 'Return url on successful payment. If blank use "/frontend/icepay/successfulPayment"'
        ));

        $this->Form()->setElement('text', 'postback-url', array(
            'value' => $this->getPostbackUrl() ?: $model->getPostbackUrl(),
            'label' => 'Postback url. This is the page to which payment information will be sent.'
        ));

        $this->Form()->setElement('text', 'fail-url', array(
            'value' => $this->getFailUrl() ?: $model->getFailUrl(),
            'label' => 'Return url on failed payment. If blank use "/frontend/icepay/failPayment"'
        ));


        $this->Form()->setElement('text', 'user_ids', array(
            'value' => '',
            'label' => 'User IDs for access test mode (separation comma)'
        ));


        $this->Form()->setElement('text', 'fail-text', array(
            'value' => '',
            'label' => 'Text which user will see when payment canceled'
        ));


        $this->Form()->setElement('text', 'pending-text', array(
            'value' => '',
            'label' => 'Text which user will see when payment pending'
        ));
    }

    public function onFrontendPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        $view->addTemplateDir(
            __DIR__ . '/Views'
        );

        $view->assign('sloganSize', $this->Config()->get('font-size'));
        $view->assign('italic', $this->Config()->get('italic'));
        $view->assign('slogan', $this->getSlogan());
        $view->assign('secret-code', $this->getSecretCode());
        $view->assign('fail-text', $this->getFailText());
        $view->assign('pending-text', $this->getPendingText());
    }

    public function getSlogan()
    {
        return array_rand(
            array_flip(
                array(
                    'An apple a day keeps the doctor away',
                    'Let’s get ready to rumble',
                    'A rolling stone gathers no moss',
                )
            )
        );
    }


    public function getFailText() {
        return $this->Config()->get('fail-text');

    }


    public function getPendingText() {
        return $this->Config()->get('pending-text');

    }


    public function getSecretCode() {
        return $this->Config()->get('secret-code');
    }


    public function getMerchantId() {
        return $this->Config()->get('merchant_id');
    }


    public function isTestMode() {
        return $this->Config()->get('test-mode');
    }


    public function getSuccessUrl() {
        return $this->Config()->get('success-url');
    }

    public function getFailUrl() {
        return $this->Config()->get('fail-url');
    }

    public function getPostbackUrl() {
        return $this->Config()->get('postback-url');

    }

    public function getTestUsersIds() {
        $usersIds = explode(',', $this->Config()->get('user_ids'));
        return $usersIds;
    }
}
