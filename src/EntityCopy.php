<?php

namespace Teamsoft\EntityCopy;

use Teamsoft\EntityCopy\Exceptions\Logic\InvalidArgumentException;

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
     * @var \Closure
     */
    protected $entityIdAllocator;

    /**
     * @var Strategy
     */
    protected $strategy;
    /**
     * @var StrategyTree
     */
    protected $strategyTree;

    /**
     * @var EntityCopyPipeline
     */
    protected $pipeline;

    /**
     * @var \Closure[]
     */
    protected $pipe = [];
    /**
     * @var \Closure[]
     */
    protected $pipeOne = [];
    /**
     * @var \Closure[]
     */
    protected $pipeMany = [];


    /**
     * Constructor
     *
     * @param object $from
     */
    public function __construct(object $from)
    {
        $this->from($from);

        $this->pipeline = new EntityCopyPipeline();
    }


    /**
     * @param object $from
     *
     * @return Strategy
     */
    public function from(object $from) : Strategy
    {
        if (! is_object($from)) {
            throw new InvalidArgumentException('From should be object');
        }

        $this->from = $from;

        return $this->strategy();
    }


    /**
     * @return object
     */
    public function create() : object
    {
        /**
         * @var Strategy[] $queueStrategy
         * @var object[]   $queueItem
         *
         * @var Strategy   $currentStrategy
         * @var object     $currentItem
         */

        $pipeline = $this->pipeline;
        $entityIdAllocator = $this->entityIdAllocator;

        $clonedRoot = clone $this->from;
        $treeRoot = $this->strategyTree->getTreeRoot();

        $graph = [];
        $tree = $this->strategyTree->getTree();

        $queueItem = [ $clonedRoot ];
        $queueStrategy = [ $treeRoot ];
        while ( null !== key($queueStrategy) ) {
            $currentItem = array_shift($queueItem);
            $currentStrategy = array_shift($queueStrategy);

            ( function (Strategy $currentStrategy) use (
                &$graph,
                &$tree,

                &$queueItem,
                &$queueStrategy,

                $pipeline,
                $entityIdAllocator
            ) {
                $strategyId = $currentStrategy->getId();

                foreach ( $currentStrategy->getOne() as $property => $class ) {
                    if (! isset($this->{$property})) {
                        continue;
                    }

                    $item = $this->{$property};
                    $itemId = ( new EntityDecorator($item, $entityIdAllocator) )->getId();
                    $itemCloned = $graph[ $class ][ $itemId ]
                        ?? clone $item;

                    $itemCloned = $pipeline->handle($itemCloned, $this, $property);

                    $itemCloned = $pipeline->handleOne($itemCloned, $this, $property);

                    $this->{$property} = $itemCloned;

                    $graph[ $class ][ $itemId ] = $itemCloned;

                    if (isset($tree[ $strategyId ][ $property ])) {
                        $queueItem[] = $itemCloned;
                        $queueStrategy[] = $tree[ $strategyId ][ $property ];
                    }
                }

                foreach ( $currentStrategy->getMany() as $property => $class ) {
                    $itemsCloned = [];

                    $iterable = $this->{$property} ?? [];
                    foreach ( $iterable as $idx => $item ) {
                        $itemId = ( new EntityDecorator($item, $entityIdAllocator) )->getId();
                        $itemCloned = $graph[ $class ][ $itemId ]
                            ?? clone $item;

                        $itemCloned = $pipeline->handle($itemCloned, $this, $property, $idx);

                        $itemsCloned[ $idx ] = $itemCloned;

                        $graph[ $class ][ $itemId ] = $itemCloned;

                        if (isset($tree[ $strategyId ][ $property ])) {
                            $queueItem[] = $itemCloned;
                            $queueStrategy[] = $tree[ $strategyId ][ $property ];
                        }
                    }

                    $itemsCloned = $pipeline->handleMany($itemsCloned, $this, $property);

                    $this->{$property} = $itemsCloned;
                }
            } )
                ->call($currentItem, $currentStrategy);
        }

        return $clonedRoot;
    }


    /**
     * @return object
     */
    public function getFrom() : object
    {
        return $this->from;
    }

    /**
     * @return Strategy
     */
    public function getStrategy() : Strategy
    {
        return $this->strategy;
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
     * @return Strategy
     */
    public function strategy() : Strategy
    {
        $this->strategyTree = new StrategyTree();

        $this->strategyTree->setRoot($strategy = new Strategy($this, $this->strategyTree));

        return $strategy;
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
     * @param \Closure $pipe
     *
     * @return static
     */
    public function pipe(\Closure $pipe) : EntityCopy
    {
        $this->pipeline->pipe($pipe);

        return $this;
    }

    /**
     * @param \Closure $pipe
     *
     * @return static
     */
    public function pipeOne(\Closure $pipe) : EntityCopy
    {
        $this->pipeline->pipeOne($pipe);

        return $this;
    }

    /**
     * @param \Closure $pipe
     *
     * @return static
     */
    public function pipeMany(\Closure $pipe) : EntityCopy
    {
        $this->pipeline->pipeMany($pipe);

        return $this;
    }
}
