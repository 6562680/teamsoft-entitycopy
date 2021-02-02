<?php

namespace Teamsoft\EntityCopy;

use Teamsoft\EntityCopy\Exceptions\Runtime\OverflowException;
use Teamsoft\EntityCopy\Exceptions\Logic\BadMethodCallException;
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
     * @param string $property
     *
     * @return bool
     */
    public function hasOneOrManyByName(string $property) : bool
    {
        return $this->hasOneByName($property)
            || $this->hasManyByName($property);
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function hasOneByName(string $property) : bool
    {
        return isset($this->one[ $property ]);
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function hasManyByName(string $property) : bool
    {
        return isset($this->many[ $property ]);
    }


    /**
     * @return Strategy
     */
    public function child() : Strategy
    {
        if (! isset($this->last)) {
            throw new BadMethodCallException('Unable to locate last item to call child(), try to call one()/many() first');
        }

        return $this->last;
    }

    /**
     * @return Strategy
     */
    public function endChild() : Strategy
    {
        if (! isset($this->parent)) {
            throw new BadMethodCallException('Unable to locate parent to call endChild(), try to call child() first');
        }

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
        if ($this->hasOneOrManyByName($property)) {
            throw new OverflowException('Property is already has a strategy: ' . $property);
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException('Class should be valid class name');
        }

        $this->one[ $property ] = $class;

        $strategy = new Strategy($this->entityCopy, $this->strategyTree, $this);

        $this->strategyTree->addNodeFor($this, $strategy, $property);

        $this->last = $strategy;

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
        if ($this->hasOneOrManyByName($property)) {
            throw new OverflowException('Property is already has a strategy: ' . $property);
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException('Class should be valid class name');
        }

        $this->many[ $property ] = $class;

        $strategy = new Strategy($this->entityCopy, $this->strategyTree, $this);

        $this->strategyTree->addNodeFor($this, $strategy, $property);

        $this->last = $strategy;

        return $this;
    }
}
