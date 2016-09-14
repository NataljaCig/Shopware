<?php

use Shopware\Plugins\Local\Frontend\IcePay\Models\IcePay as Icepay;
use Shopware\Plugins\Local\Frontend\IcePay\Components\IcePayQuerySender as IcepayQuerySender;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Shopware\CustomModels\IcePayPayments as IcePayPayments;

/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Backend_IcePayBackend extends Enlight_Controller_Action
{
    /**
     * @var \Shopware\Models\Site\Repository
     */
    protected $supplierRepository = null;

    /**
     * Emotion repository. Declared for an fast access to the emotion repository.
     *
     * @var \Shopware\Models\Emotion\Repository
     * @access private
     */
    public static $emotionRepository = null;

    /**
     * @var \Shopware\Models\Form\Repository
     */
    protected $formRepository = null;

    /**
     * Internal helper function to get access to the form repository.
     *
     * @return \Shopware\Models\Article\Repository
     */
    private function getSupplierRepository()
    {
        if ($this->supplierRepository === null) {
            $this->supplierRepository = $this->getModelManager()->getRepository('Shopware\Models\Article\Article');
        }

        return $this->supplierRepository;
    }

    private function getFormRepository()
    {
        if ($this->formRepository === null) {
            $this->formRepository = $this->getModelManager()->getRepository('Shopware\Models\Config\Form');
        }

        return $this->formRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return null|Shopware\Models\Emotion\Repository
     */
    protected function getEmotionRepository()
    {
        if (self::$emotionRepository === null) {
            self::$emotionRepository = $this->getModelManager()->getRepository('Shopware\Models\Emotion\Emotion');
        }

        return self::$emotionRepository;
    }



    public function indexAction()
    {
        $payments = Shopware()->Models()->createQuery("SELECT p FROM Shopware\CustomModels\IcePayPayments p WHERE p.state_backend = 1 ORDER BY p.position")->getResult();
        $issuers = Shopware()->Models()->createQuery("SELECT i FROM Shopware\CustomModels\IcePayIssuers i WHERE i.state_backend = 1 ORDER BY i.position")->getResult();
        $this->View()->assign('payments', $payments);
        $this->View()->assign('issuers', $issuers);
        $this->View()->assign('uploadDir', "/files/images/icepay/");
    }

    public function refreshPaymentsAction() {
        $icePayPayments = new IcePayPayments();
        $icePayPayments->updatePayments();
    }

    public function updateCustomPaymetsAction() {
        $requestData = $this->Request()->getParams();

        $uploadDirectory = __DIR__."/../../../../../../../../files/images/icepay";

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777);
        }

        foreach ($requestData['PaymentCode'] as $key => $paymentCode) {
            $payment = Shopware()->Models()->createQuery("SELECT p FROM Shopware\CustomModels\IcePayPayments p WHERE p.payment_code = '{$paymentCode}'")
                ->getResult();

            $payment = $payment[0];
            $tmp_name = $_FILES["PaymentImage"]["tmp_name"][$key];
            $name = $_FILES["PaymentImage"]["name"][$key];
            if ($tmp_name) {
                $payment->image = $name;
                if (!file_exists($uploadDirectory."/".$name)) {
                    move_uploaded_file($tmp_name, $uploadDirectory."/".$name);
                }
            }

            $payment->name = $requestData['PaymentName'][$key];
            $payment->position = $requestData['PaymentPosition'][$key];
            $state = false;
            foreach ($requestData['PaymentState'] as $paymentState) {
                if ($paymentState == $payment->id) {
                    $state = true;
                }
            }
            $payment->state = $state;

            Shopware()->Models()->persist($payment);
            Shopware()->Models()->flush();
        }
        foreach ($requestData['IssuerCode'] as $key => $issuerCode) {
            $issuer = Shopware()->Models()->createQuery("SELECT i FROM Shopware\CustomModels\IcePayIssuers i WHERE i.issuer_code = '{$issuerCode}'")
                ->getResult();
            $issuer = $issuer[0];
            $issuer->name = $requestData['IssuerName'][$key];
            $issuer->position = $requestData['IssuerPosition'][$key];
            $state = 0;
            foreach ($requestData['IssuerState'] as $issuerState) {
                if ($issuerState == $issuer->id) {
                    $state = 1;
                }
            }
            $issuer->state = $state;

            Shopware()->Models()->persist($issuer);
            Shopware()->Models()->flush();
        }
    }

    public function listAction()
    {
        $filter = null;
        $sort = [['property' => 'name']];
        $limit = 25;
        $offset = 0;

        $query = $this->getSupplierRepository()->getSupplierListQuery($filter, $sort, $limit, $offset);
        $total = $this->getModelManager()->getQueryCount($query);
        $suppliers = $query->getArrayResult();

        $this->View()->assign(['suppliers' => $suppliers, 'totalSuppliers' => $total]);
    }

    public function emotionAction()
    {
    }

    public function getEmotionAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer();

        $limit = $this->Request()->getParam('limit', null);
        $offset = $this->Request()->getParam('start', 0);
        $filter = $this->Request()->getParam('filter', null);
        $filterBy = $this->Request()->getParam('filterBy', null);
        $categoryId = $this->Request()->getParam('categoryId', null);

        $query = $this->getEmotionRepository()->getListingQuery($filter, $filterBy, $categoryId);

        $query->setFirstResult($offset)->setMaxResults($limit);

        /**@var $statement PDOStatement */
        $statement = $query->execute();
        $emotions = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(['emotions' => $emotions]);
    }

    public function configAction()
    {
        $repository = $this->getFormRepository();

        $user = Shopware()->Auth()->getIdentity();
        /** @var $locale \Shopware\Models\Shop\Locale */
        $locale = $user->locale;
        $filter = [['property' => 'id', 'value' => 133]];

        /** @var $builder \Shopware\Components\Model\QueryBuilder */
        $builder = $repository->createQueryBuilder('form')
            ->select(array('form', 'element', 'value', 'elementTranslation', 'formTranslation'))
            ->leftJoin('form.elements', 'element')
            ->leftJoin('form.translations', 'formTranslation', Join::WITH, 'formTranslation.localeId = :localeId')
            ->leftJoin('element.translations', 'elementTranslation', Join::WITH, 'elementTranslation.localeId = :localeId')
            ->leftJoin('element.values', 'value')
            ->setParameter("localeId", $locale->getId());

        $builder->addOrderBy((array) $this->Request()->getParam('sort', array()))
            ->addFilter($filter);

        $data = $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        foreach ($data['elements'] as &$values) {
            foreach ($values['translations'] as $array) {
                if ($array['label'] !== null) {
                    $values['label'] = $array['label'];
                }
                if ($array['description'] !== null) {
                    $values['description'] = $array['description'];
                }
            }

            if (!in_array($values['type'], array('select', 'combo'))) {
                continue;
            }
        }

        $this->View()->assign(['data' => $data]);
    }

    public function createSubWindowAction()
    {
    }
}
