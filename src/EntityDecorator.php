<?php

namespace Teamsoft\EntityCopy;

/**
 * Class EntityDecorator
 */
class EntityDecorator implements EntityInterface
{
    use EntityTrait;


    /**
     * @var object
     */
    protected $entity;
    /**
     * @var \Closure
     */
    protected $idAllocator;


    /**
     * Constructor
     *
     * @param object        $entity
     * @param null|\Closure $idAllocator
     */
    public function __construct(object $entity, \Closure $idAllocator = null)
    {
        $this->entity = $entity;
        $this->idAllocator = $idAllocator;
    }


    /**
     * @return null|string
     */
    public function getId() : ?string
    {
        if (isset($this->id)) {
            return $this->id;
        }

        if ($this->idAllocator) {
            $id = $this->idAllocator->call($this->entity);

        } elseif (is_a($this->entity, EntityInterface::class)) {
            $id = $this->entity->getId();

        } elseif (isset($this->entity->id)) {
            $id = $this->entity->id;

        } else {
            $id = $this->entity->getId();

        }

        return $this->id = $id;
    }


    /**
     * @return object
     */
    public function getEntity() : object
    {
        return $this->entity;
    }
}
