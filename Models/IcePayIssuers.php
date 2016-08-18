<?php
/**
 * Created by PhpStorm.
 * User: future
 * Date: 15/08/16
 * Time: 22:48
 */

//namespace Shopware\Plugins\Local\Frontend\IcePay\Models;

namespace Shopware\CustomModels;

use Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection,
    Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity(repositoryClass="Shopware\CustomModels\IcePayIssuers")
 * @ORM\Table(name="s_ice_pay_issuers")
 */
class IcePayIssuers extends ModelEntity
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
     * @var string $issuer_code
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="issuer_code", type="string", length=255, nullable=false)
     */
    public $issuer_code;

    /**
     * @var string $name
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    public $name;

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
     * @var integer $payment_id
     *
     * @ORM\Column(name="payment_id", type="integer", nullable=true)
     */
    public $payment_id = null;

    public function setArray(array $attributes, $order, $payment_id) {
        $this->name = $attributes['Description'];
        $this->issuer_code = $attributes['IssuerKeyword'];
        $this->payment_id = $payment_id;
        $this->position = $order;
        $this->state = 0;
    }

}