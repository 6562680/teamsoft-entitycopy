<?php

namespace Teamsoft\EntityCopy;

/**
 * Class ResultMapperQueue
 */
class ResultMapperQueue
{
    /**
     * @var \Closure[]
     */
    protected $mappers = [];


    /**
     * @param \Closure $mapper
     *
     * @return static
     */
    public function map(\Closure $mapper) : ResultMapperQueue
    {
        $this->mappers[] = $mapper;

        return $this;
    }


    /**
     * @param mixed    $itemCloned
     * @param mixed    $itemOriginal
     * @param object   $itemParentTo
     * @param object   $itemParentFrom
     * @param string   $property
     *
     * @param Strategy $strategy
     *
     * @return object
     */
    public function handle(
        $itemCloned,
        $itemOriginal,

        object $itemParentTo,
        object $itemParentFrom,
        string $property,

        Strategy $strategy
    ) : object
    {
        foreach ( $this->mappers as $mapper ) {
            $itemCloned = $mapper(
                $itemCloned,
                $itemOriginal,

                $itemParentTo,
                $itemParentFrom,
                $property,

                $strategy
            );
        }

        return $itemCloned;
    }
}
