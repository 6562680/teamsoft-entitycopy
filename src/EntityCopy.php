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
    protected $resultOneMapper;
    /**
     * @var ResultMapperQueue
     */
    protected $resultManyMapper;


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

        $this->resultOneMapper = new ResultMapperQueue();
        $this->resultManyMapper = new ResultMapperQueue();
    }


    /**
     * @return Strategy
     */
    public function newStrategy() : Strategy
    {
        $this->strategyTree = new StrategyTree();

        $this->strategyTree->setRootNode($strategy = new Strategy($this, $this->strategyTree));

        return $strategy;
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
     * @return object
     */
    public function build() : object
    {
        $strategy = $this->strategyTree->getRootNode();
        $from = $this->from;
        $to = clone $from;

        $graph = [];
        $queue = [
            [ $strategy, $from, $to ],
        ];
        while ( null !== key($queue) ) {
            [
                $currentStrategy,
                $currentFrom,
                $currentTo,
            ] = array_shift($queue);

            dump(['currentStrategy', $currentStrategy ]);

            $this->buildItem(
                $currentStrategy,

                $currentFrom,
                $currentTo,

                $queue,
                $graph
            );
        }

        return $to;
    }


    /**
     * @param Strategy $strategy
     * @param object   $from
     * @param object   $to
     * @param array    $queue
     * @param array    $graph
     *
     * @return EntityCopy
     */
    protected function buildItem(
        Strategy $strategy,

        object $from,
        object $to,

        &$queue = [],
        &$graph = []
    ) : EntityCopy
    {
        $strategyId = $strategy->getId();

        foreach ( $strategy->getOne() as $property => $class ) {
            if (! $childFrom = $this->objectGetProperty($from, $property)) {
                continue;
            }

            $childTo = $this->cloneItem($strategy, $childFrom, $to, $from, $property, $graph);
            dd([ $childFrom, $childTo, $from, $to ]);

            if ($childStrategy = $this->strategyTree->getChildByIdFor($strategyId, $property)) {
                $queue[] = [ $childStrategy, $childFrom, $childTo ];
            }

            $this->objectSetProperty($to, $property, $childTo);
        }

        foreach ( $strategy->getMany() as $property => $class ) {
            if (! $childrenFrom = $this->objectGetProperty($from, $property)) {
                continue;
            }
            dd($childrenFrom);

            $childrenTo = [];
            foreach ( $childrenFrom as $idx => $childFrom ) {
                $childTo = $this->cloneItem($strategy, $childFrom, $to, $from, $property, $graph);

                $childrenTo[ $idx ] = $childTo;

                if ($childStrategy = $this->strategyTree->getChildByIdFor($strategyId, $property)) {
                    $queue[] = [ $childStrategy, $childFrom, $childTo ];
                }
            }

            $childrenTo = $this->resultManyMapper->handle(
                $childrenTo, $childrenFrom,
                $to, $from, $property,
                $strategy
            );

            $this->objectSetProperty($to, $property, $childrenTo);
        }

        return $this;
    }

    /**
     * @param Strategy $strategy
     *
     * @param object   $item
     * @param object   $to
     * @param object   $from
     * @param string   $property
     *
     * @param array    $graph
     *
     * @return mixed
     */
    protected function cloneItem(
        Strategy $strategy,

        object $item,

        object $to,
        object $from,
        string $property,

        &$graph = []
    )
    {
        $entity = new EntityDecorator($item, $this->entityIdAllocator);
        $entityId = $entity->getId();

        $class = get_class($item);

        if (isset($graph[ $class ][ $entityId ])) {
            $itemCloned = $graph[ $class ][ $entityId ];

        } else {
            $itemCloned = clone $item;
            $itemCloned = $this->resultOneMapper->handle(
                $itemCloned, $item,
                $to, $from, $property,
                $strategy
            );

            $graph[ $class ][ $entityId ] = $itemCloned;
        }

        return $itemCloned;
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
        return $this->strategyTree
            ? $this->strategyTree->toArray()
            : [];
    }


    /**
     * @param mixed ...$mappers
     *
     * @return static
     */
    public function mapOne(...$mappers) : EntityCopy
    {
        foreach ( $mappers as $r ) {
            $this->resultOneMapper->map($r);
        }

        return $this;
    }

    /**
     * @param mixed ...$mappers
     *
     * @return static
     */
    public function mapMany(...$mappers) : EntityCopy
    {
        foreach ( $mappers as $r ) {
            $this->resultManyMapper->map($r);
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
