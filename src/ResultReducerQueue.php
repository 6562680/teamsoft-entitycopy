<?php

namespace Teamsoft\EntityCopy;

/**
 * Class ResultReducerQueue
 */
class ResultReducerQueue
{
    /**
     * @var mixed
     */
    protected $carry;
    /**
     * @var \Closure[]
     */
    protected $reducers = [];


    /**
     * Constructor
     *
     * @param mixed $carry
     */
    public function __construct($carry)
    {
        $this->carry = $carry;
    }


    /**
     * @return mixed
     */
    public function getCarry()
    {
        return $this->carry;
    }


    /**
     * @param \Closure $reducer
     *
     * @return static
     */
    public function reduce(\Closure $reducer) : ResultReducerQueue
    {
        $this->reducers[] = $reducer;

        return $this;
    }


    /**
     * @param object      $itemCloned
     * @param null|string $idx
     * @param object      $itemOriginal
     * @param object      $itemParent
     * @param string      $property
     * @param Strategy    $strategy
     *
     * @return object
     */
    public function handle(
        object $itemCloned,
        string $idx,
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

        foreach ( $this->reducers as $reducer ) {
            $this->carry = $reducer($this->carry, $itemCloned, $idx, $itemOriginal, $itemParent, $options);
        }

        return $this->carry;
    }
}
