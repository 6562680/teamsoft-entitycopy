<?php

namespace Teamsoft\EntityCopy;

use Teamsoft\EntityCopy\Exceptions\Logic\InvalidArgumentException;

/**
 * Trait EntityTrait
 */
trait EntityTrait
{
    /**
     * @var string
     */
    protected $id;


    /**
     * @return null|string
     */
    public function getId() : ?string
    {
        return $this->id;
    }
}
