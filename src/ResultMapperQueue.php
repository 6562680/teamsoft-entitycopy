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
     * @param object   $itemCloned
     * @param object   $itemParent
     * @param string   $property
     * @param object   $itemOriginal
     * @param Strategy $strategy
     *
     * @return object
     */
    public function handle(
        object $itemCloned,
        object $itemOriginal,
        object $itemParent,
        string $property,
        Strategy $strategy
    ) : object
    {
        $options = [
            'item_original' => $itemOriginal,
            'item_parent'   => $itemParent,
            'property'      => $property,
            'strategy'      => $strategy,
        ];

        foreach ( $this->mappers as $mapper ) {
            $itemCloned = $mapper($itemCloned, $itemOriginal, $itemParent, $options);
        }

        return $itemCloned;
    }
}
