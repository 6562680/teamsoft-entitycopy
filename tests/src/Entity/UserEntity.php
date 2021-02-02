<?php

namespace Teamsoft\EntityCopy\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class UserEntity extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     */
    public $login;


    /**
     * @ORM\OneToOne(targetEntity="UserProfileEntity", mappedBy="user")
     * @var UserProfileEntity
     */
    public $userProfile;

    /**
     * @ORM\OneToMany(targetEntity="UserWalletEntity", mappedBy="user")
     * @var UserWalletEntity[]|ArrayCollection
     */
    public $userWallets;

    /**
     * @ORM\ManyToMany(targetEntity="EmailEntity", inversedBy="emailUsers")
     * @ORM\JoinTable(name="users_emails",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="email_id", referencedColumnName="id")}
     * )
     * @var EmailEntity[]|ArrayCollection
     */
    public $userEmails;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userWallets = new ArrayCollection();
        $this->userEmails = new ArrayCollection();
    }

    /**
     * Cloner
     */
    public function __clone()
    {
        parent::__clone();

        $this->userProfile = null;

        $this->userWallets->clear();
        $this->userEmails->clear();
    }
}
