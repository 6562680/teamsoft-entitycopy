<?php

namespace Teamsoft\EntityCopy\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_emails")
 */
class EmailEntity extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    public $email;


    /**
     * @ORM\ManyToMany (targetEntity="UserEntity", inversedBy="userEmails")
     * @ORM\JoinTable(?)
     * @var UserEntity[]|ArrayCollection
     */
    public $users;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }
}
