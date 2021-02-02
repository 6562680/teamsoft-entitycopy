<?php

namespace Teamsoft\EntityCopy\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_wallets")
 */
class UserWalletEntity extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    public $currency;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    public $value_calculated;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    public $calculated_at;


    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="userEmails")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @var string
     */
    public $user;


    /**
     * Cloner
     */
    public function __clone()
    {
        parent::__clone();

        $this->user = null;
    }
}
