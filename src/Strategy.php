<?php

namespace Teamsoft\EntityCopy;

use Teamsoft\EntityCopy\Exceptions\Runtime\OverflowException;
use Teamsoft\EntityCopy\Exceptions\Logic\InvalidArgumentException;

/**
 * Class Strategy
 */
class Strategy
{
    /**
     * @var EntityCopy
     */
    protected $entityCopy;
    /**
     * @var StrategyTree
     */
    protected $strategyTree;
    /**
     * @var null|Strategy
     */
    protected $parent;

    /**
     * @var Strategy
     */
    protected $last;

    /**
     * @var string[]
     */
    protected $one = [];
    /**
     * @var string[]
     */
    protected $many = [];


    /**
     * Strategy constructor.
     *
     * @param EntityCopy    $entityCopy
     * @param StrategyTree  $strategyTree
     * @param null|Strategy $parent
     */
    public function __construct(
        EntityCopy $entityCopy,
        StrategyTree $strategyTree,

        Strategy $parent = null
    )
    {
        $this->entityCopy = $entityCopy;
        $this->strategyTree = $strategyTree;

        $this->parent = $parent;
    }


    /**
     * @return object
     */
    public function build() : object
    {
        return $this->entityCopy->build();
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return spl_object_id($this);
    }


    /**
     * @return string[]
     */
    public function getOne() : array
    {
        return $this->one;
    }

    /**
     * @return string[]
     */
    public function getMany() : array
    {
        return $this->many;
    }


    /**
     * @param Strategy $last
     */
    protected function setLast(Strategy $last)
    {
        $this->last = $last;
    }


    /**
     * @return Strategy
     */
    public function child() : Strategy
    {
        return $this->last;
    }

    /**
     * @return Strategy
     */
    public function endChild() : Strategy
    {
        return $this->parent;
    }


    /**
     * @param string $property
     * @param string $class
     *
     * @return $this
     */
    public function one(string $property, string $class) : Strategy
    {
        if (isset($this->one[ $property ]) || isset($this->many[ $property ])) {
            throw new OverflowException('Property is already has a strategy: ' . $property);
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException('Class should be valid class name');
        }

        if (! property_exists($class, $property)) {
            throw new InvalidArgumentException('Property is not exists in class: ' . $class);
        }

        $this->one[ $property ] = $class;

        $this->last = new Strategy($this->entityCopy, $this->strategyTree, $this);

        $this->strategyTree->addNodeFor($this, $this->last, $property);

        return $this;
    }

    /**
     * @param string $property
     * @param string $class
     *
     * @return $this
     */
    public function many(string $property, string $class) : Strategy
    {
        if (isset($this->one[ $property ]) || isset($this->many[ $property ])) {
            throw new OverflowException('Property is already has a strategy: ' . $property);
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException('Class should be valid class name');
        }

        if (! property_exists($class, $property)) {
            throw new InvalidArgumentException('Property is not exists in class: ' . $class);
        }

        $this->many[ $property ] = $class;

        $this->last = new Strategy($this->entityCopy, $this->strategyTree, $this);

        $this->strategyTree->addNodeFor($this, $this->last, $property);

        return $this;
    }
}
