<?php

namespace Teamsoft\EntityCopy\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="emails")
 */
class EmailEntity extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    public $email;


    /**
     * @ORM\ManyToMany(targetEntity="UserEntity", inversedBy="userEmails")
     * @ORM\JoinTable(name="users_emails",
     *     joinColumns={@ORM\JoinColumn(name="email_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     * @var UserEntity[]|ArrayCollection
     */
    public $emailUsers;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->emailUsers = new ArrayCollection();
    }


    /**
     * Cloner
     */
    public function __clone()
    {
        parent::__clone();

        $this->emailUsers->clear();
    }
}
