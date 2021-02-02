<?php

namespace Teamsoft\EntityCopy\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_profiles")
 */
class UserProfileEntity extends AbstractEntity
{
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    public $registered_at;


    /**
     * @ORM\OneToOne(targetEntity="UserEntity", inversedBy="userProfile")
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
