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
     * @ORM\Column(name="state", type="integer", nullable=true)
     */
    public $state = null;


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
        foreach ($response['PaymentMethods'] as $key => $paymentMethod) {
            $payment = Shopware()->Models()->createQuery("SELECT p FROM Shopware\CustomModels\IcePayPayments p WHERE p.payment_code = '{$paymentMethod['PaymentMethodCode']}'")
                ->getResult();
            if (empty($payment)) {
                $payment = new self();
                $payment->name = $paymentMethod['Description'];
                $payment->payment_code = $paymentMethod['PaymentMethodCode'];
                $payment->position = $key + 1;
                $payment->state = 0;

                Shopware()->Models()->persist($payment);
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
                }
            }
        }
    }
}