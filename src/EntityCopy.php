<?php

namespace Teamsoft\EntityCopy;

/**
 * Class EntityCopy
 */
class EntityCopy
{
    /**
     * @var object
     */
    protected $from;

    /**
     * @var StrategyTree
     */
    protected $strategyTree;

    /**
     * @var \Closure
     */
    protected $entityIdAllocator;

    /**
     * @var ResultMapperQueue
     */
    protected $resultMapper;
    /**
     * @var ResultReducerQueue
     */
    protected $resultReducer;


    /**
     * Constructor
     *
     * @param null|object $from
     */
    public function __construct(object $from = null)
    {
        if (isset($from)) {
            $this->from($from);
        }

        $this->resultMapper = new ResultMapperQueue();
        $this->resultReducer = new ResultReducerQueue($carry = []);
    }


    /**
     * @return Strategy
     */
    public function newStrategy() : Strategy
    {
        $this->strategyTree = new StrategyTree();

        $this->strategyTree->setRoot($strategy = new Strategy($this, $this->strategyTree));

        return $strategy;
    }


    /**
     * @param \Closure[] ...$mappers
     *
     * @return EntityCopy
     */
    public function newMapper(...$mappers) : EntityCopy
    {
        $this->resultMapper = new ResultMapperQueue();

        $this->map(...$mappers);

        return $this;
    }

    /**
     * @param mixed      $carry
     * @param \Closure[] ...$reducers
     *
     * @return EntityCopy
     */
    public function newReducer($carry, ...$reducers) : EntityCopy
    {
        $this->resultReducer = new ResultReducerQueue($carry);

        $this->reduce(...$reducers);

        return $this;
    }


    /**
     * @param object $from
     *
     * @return Strategy
     */
    public function from(object $from) : Strategy
    {
        $this->from = $from;

        $strategy = $this->newStrategy();

        return $strategy;
    }


    /**
     * @return Strategy
     */
    public function strategy() : Strategy
    {
        return $this->newStrategy();
    }


    /**
     * @param null|object $from
     *
     * @return object
     */
    public function build(object $from = null) : object
    {
        $this->from = $from
            ?? $this->from;

        $fromCloned = clone $this->from;
        $strategyRoot = $this->strategyTree->getTreeRoot();

        $graph = [];
        $queueItem = [ $fromCloned ];
        $queueStrategy = [ $strategyRoot ];
        while ( null !== key($queueStrategy) ) {
            $currentItem = array_shift($queueItem);
            $currentStrategy = array_shift($queueStrategy);

            $this->buildItem(
                $currentItem,
                $currentStrategy,

                $queueItem,
                $queueStrategy,

                $graph
            );
        }

        return $fromCloned;
    }

    /**
     * @param object   $item
     * @param Strategy $strategy
     *
     * @param array    $queueItem
     * @param array    $queueStrategy
     *
     * @param array    $graph
     *
     * @return EntityCopy
     */
    protected function buildItem(
        object $item,
        Strategy $strategy,

        &$queueItem = [],
        &$queueStrategy = [],

        &$graph = []
    ) : EntityCopy
    {
        $strategyTree = $this->strategyTree->getTree();

        foreach ( $strategy->getOne() as $property => $class ) {
            if (! $child = $this->objectGetProperty($item, $property)) {
                continue;
            }

            $childCloned = $this->cloneItem($strategy, $child, $item, $property, $graph);

            if (isset($strategyTree[ $strategy->getId() ][ $property ])) {
                $childStrategy = $strategyTree[ $strategy->getId() ][ $property ];

                $queueItem[] = $childCloned;
                $queueStrategy[] = $childStrategy;
            }

            $this->objectSetProperty($item, $property, $childCloned);
        }

        foreach ( $strategy->getMany() as $property => $class ) {
            if (! $children = $this->objectGetProperty($item, $property)) {
                continue;
            }

            $childrenCloned = [];
            foreach ( $children as $idx => $child ) {
                $childCloned = $this->cloneItem($strategy, $child, $item, $property, $graph);

                $childrenCloned[ $idx ] = $childCloned;

                if (isset($strategyTree[ $strategy->getId() ][ $property ])) {
                    $childStrategy = $strategyTree[ $strategy->getId() ][ $property ];

                    $queueItem[] = $childCloned;
                    $queueStrategy[] = $childStrategy;
                }
            }

            foreach ( $childrenCloned as $idx => $childCloned ) {
                $child = $children[ $idx ];

                $this->resultReducer->handle($childCloned, $idx, $child, $item, $property, $strategy);
            }

            $this->objectSetProperty($item, $property, $this->resultReducer->getCarry());
        }

        return $this;
    }

    /**
     * @param Strategy $strategy
     *
     * @param object   $child
     * @param object   $item
     * @param string   $property
     *
     * @param array    $graph
     *
     * @return mixed
     */
    protected function cloneItem(
        Strategy $strategy,

        object $child,
        object $item,
        string $property,

        &$graph = []
    )
    {
        $class = get_class($child);

        $entity = new EntityDecorator($child, $this->entityIdAllocator);
        $entityId = $entity->getId();

        if (isset($graph[ $class ][ $entityId ])) {
            $childCloned = $graph[ $class ][ $entityId ];

        } else {
            $childCloned = clone $child;
            $childCloned = $this->resultMapper->handle($childCloned, $child, $item, $property, $strategy);

            $graph[ $class ][ $entityId ] = $childCloned;
        }

        return $childCloned;
    }


    /**
     * @return object
     */
    public function getFrom() : object
    {
        return $this->from;
    }


    /**
     * @return StrategyTree
     */
    public function getStrategyTree() : StrategyTree
    {
        return $this->strategyTree;
    }


    /**
     * @param \Closure $entityIdAllocator
     *
     * @return static
     */
    public function setEntityIdAllocator(\Closure $entityIdAllocator) : EntityCopy
    {
        $this->entityIdAllocator = $entityIdAllocator;

        return $this;
    }


    /**
     * @return array
     */
    public function strategyToArray() : array
    {
        $tree = $this->strategyTree->getTree();

        $result = ( $fn = function (Strategy $currentStrategy) use (&$fn, &$tree) {
            $result = [];

            $strategyId = $currentStrategy->getId();

            if (! isset($tree[ $strategyId ])) {
                return $result;
            }

            foreach ( $tree[ $strategyId ] as $property => $childStrategy ) {
                if (isset($tree[ $childStrategy->getId() ])) {
                    // ! recursion
                    $result[ $property ] = $fn($childStrategy);

                } else {
                    foreach ( $currentStrategy->getOne() as $childProperty => $childClass ) {
                        $result[ $childProperty ] = $childClass;
                    }

                    foreach ( $currentStrategy->getMany() as $childProperty => $childClass ) {
                        $result[ $childProperty ] = $childClass;
                    }
                }
            }

            return $result;
        } )($this->strategyTree->getTreeRoot());

        return $result;
    }


    /**
     * @param mixed ...$mappers
     *
     * @return static
     */
    public function map(...$mappers) : EntityCopy
    {
        foreach ( $mappers as $r ) {
            $this->resultMapper->map($r);
        }

        return $this;
    }

    /**
     * @param mixed ...$reducers
     *
     * @return static
     */
    public function reduce(...$reducers) : EntityCopy
    {
        foreach ( $reducers as $r ) {
            $this->resultReducer->reduce($r);
        }

        return $this;
    }


    /**
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected function objectGetProperty(object $object, string $property)
    {
        $fnGetter = function () use ($property) {
            return $this->{$property} ?? null;
        };

        return $fnGetter->call($object);
    }

    /**
     * @param object $object
     * @param string $property
     * @param        $value
     *
     * @return EntityCopy
     */
    protected function objectSetProperty(object $object, string $property, $value) : EntityCopy
    {
        ( function ($value) use ($property) {
            $this->{$property} = $value;
        } )
            ->call($object, $value);

        return $this;
    }
}
