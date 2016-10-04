<?php

//namespace Shopware\Plugins\Local\Frontend\IcePay\Models;
namespace Shopware\CustomModels;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection,
    Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Shopware\Plugins\Local\Frontend\IcePay\Components\IcePayQuerySender as IcePayQuerySender,
    Shopware\Plugins\Local\Frontend\IcePay\Models\IcePay as Icepay;

/**
 * @ORM\Entity(repositoryClass="Shopware\CustomModels\IcePayPayments")
 * @ORM\Table(name="s_ice_pay_payments")
 */
class IcePayPayments extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var string $name
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    public $name;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    public $image = null;

    /**
     * @var string $payment_code
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="payment_code", type="string", length=255, nullable=false)
     */
    public $payment_code;


    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    public $position = null;


    /**
     * @var integer $state
     *
     * @ORM\Column(name="state", type="integer", nullable=false)
     */
    public $state = 0;

    /**
     * @var integer $state
     *
     * @ORM\Column(name="state_backend", type="integer", nullable=false)
     */
    public $state_backend = 0;


    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @Assert\Valid
     *
     * @ORM\OneToMany(targetEntity="Shopware\CustomModels\IcePayIssuers", mappedBy="icepaypayments", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    public $issuers;


    public function updatePayments() {
        $model = new IcePay();
        $methodUrl = IcePay::GET_PAYMENT_METHODS;

        $queryArray = [
            "Timestamp" => date('Y-m-d\TH:i:s\Z'),
        ];

        $model->setQueryParams($queryArray);
        $headers = $model->getHeaders($methodUrl);
        $querySender = new IcePayQuerySender($methodUrl, $headers, $queryArray);
        $response = $querySender->sendQuery();
        if (!isset($response['Message'])) {
            $sql= "UPDATE s_ice_pay_payments SET state_backend = 0 WHERE 1";
            Shopware()->Db()->query($sql);

            $sql= "UPDATE s_ice_pay_issuers SET state_backend = 0 WHERE 1";
            Shopware()->Db()->query($sql);
        } else {
            var_dump('Have some problem');die();
        }
        foreach ($response['PaymentMethods'] as $key => $paymentMethod) {
            $payment = Shopware()->Models()->createQuery("SELECT p FROM Shopware\CustomModels\IcePayPayments p WHERE p.payment_code = '{$paymentMethod['PaymentMethodCode']}'")
                ->getResult();
            if (empty($payment)) {
                $payment = new self();
                $payment->name = $paymentMethod['Description'];
                $payment->payment_code = $paymentMethod['PaymentMethodCode'];
                $payment->position = $key + 1;
                $payment->state = 0;
                $payment->state_backend = 1;

                Shopware()->Models()->persist($payment);
                Shopware()->Models()->flush();
            } else {
                $payment[0]->state_backend = 1;

                Shopware()->Models()->persist($payment[0]);
                Shopware()->Models()->flush();

            }
            foreach ($paymentMethod['Issuers'] as $issuerKey => $issuer) {
                $issuerExists = Shopware()->Models()->createQuery("SELECT i FROM Shopware\CustomModels\IcePayIssuers i WHERE i.issuer_code = '{$issuer['IssuerKeyword']}'")
                    ->getResult();
                if (empty($issuerExists)) {
                    $issuerModel = new IcePayIssuers();
                    if (is_array($payment)) {
                        $payment_id = $payment[0]->id;
                    } else {
                        $payment_id = $payment->id;
                    }
                    $issuerModel->setArray($issuer, $issuerKey + 1, $payment_id);

                    Shopware()->Models()->persist($issuerModel);
                    Shopware()->Models()->flush();
                } else {
                    $issuerExists[0]->state_backend = 1;

                    Shopware()->Models()->persist($issuerExists[0]);
                    Shopware()->Models()->flush();
                }
            }
        }
    }
}