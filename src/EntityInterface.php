<?php

namespace Teamsoft\EntityCopy;

use Teamsoft\EntityCopy\Exceptions\Logic\InvalidArgumentException;

/**
 * Interface EntityInterface
 */
interface EntityInterface
{
    /**
     * @return null|string
     */
    public function getId() : ?string;
}
