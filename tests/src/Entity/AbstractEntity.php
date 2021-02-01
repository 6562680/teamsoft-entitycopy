<?php

namespace Teamsoft\EntityCopy\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var string
     */
    public $id;


    /**
     * Cloner
     */
    public function __clone()
    {
        $this->id = null;
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
}
