<?php

namespace Teamsoft\EntityCopy;

/**
 * Class StrategyTree
 */
class StrategyTree
{
    /**
     * @var Strategy
     */
    protected $rootNode;

    /**
     * @var Strategy[]
     */
    protected $list = [];
    /**
     * @var string[][][]
     */
    protected $index = [
        'parent_id' => [],
    ];


    /**
     * @return Strategy
     */
    public function getRootNode() : Strategy
    {
        return $this->rootNode;
    }


    /**
     * @return Strategy[]
     */
    public function getList() : array
    {
        return $this->list;
    }

    /**
     * @return string[][]
     */
    public function getIndex() : array
    {
        return $this->index;
    }


    /**
     * @param string $id
     *
     * @return Strategy
     */
    public function getNodeById(string $id) : ?Strategy
    {
        return $this->list[ $id ] ?? null;
    }

    /**
     * @param string $id
     *
     * @return Strategy[]
     */
    public function getChildrenById(string $id) : array
    {
        $result = [];

        if (! $this->hasChildrenById($id)) {
            return $result;
        }

        foreach ( $this->index[ 'parent_id' ][ $id ] as $property => $childId ) {
            $result[ $property ] = $this->list[ $childId ];
        }

        return $result;
    }

    /**
     * @param string $id
     * @param string $property
     *
     * @return Strategy
     */
    public function getChildByIdFor(string $id, string $property) : ?Strategy
    {
        if (! $this->hasChildByIdFor($id, $property)) {
            return null;
        }

        return $this->list[ $this->index[ 'parent_id' ][ $id ][ $property ] ];
    }


    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasNodeById(string $id) : bool
    {
        return isset($this->list[ $id ]);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasChildrenById(string $id) : bool
    {
        return isset($this->index[ 'parent_id' ][ $id ]);
    }


    /**
     * @param string $id
     * @param string $property
     *
     * @return bool
     */
    public function hasChildByIdFor(string $id, string $property) : bool
    {
        return isset($this->index[ 'parent_id' ][ $id ][ $property ])
            && isset($this->list[ $this->index[ 'parent_id' ][ $id ][ $property ] ]);
    }


    /**
     * @param Strategy $root
     *
     * @return StrategyTree
     */
    public function setRootNode(Strategy $root) : StrategyTree
    {
        $this->rootNode = $root;

        $this->list[ $id = $root->getId() ] = $root;

        return $this;
    }


    /**
     * @param Strategy $parent
     * @param Strategy $node
     * @param string   $property
     *
     * @return StrategyTree
     */
    public function addNodeFor(Strategy $parent, Strategy $node, string $property) : StrategyTree
    {
        $parentId = $parent->getId();
        $id = $node->getId();

        $this->list[ $id ] = $node;
        $this->index[ 'parent_id' ][ $parentId ][ $property ] = $id;

        return $this;
    }


    /**
     * @return array
     */
    public function toArray() : array
    {
        return ( $fn = function (Strategy $strategy) use (&$fn) {
            $result = [];

            $strategyId = $strategy->getId();

            if (! isset($this->index[ $strategyId ])) {
                return $result;
            }

            foreach ( $this->index[ 'parent_id' ][ $strategyId ] as $property => $childStrategyId ) {
                $childStrategy = $this->list[ $childStrategyId ];

                if ($this->hasChildrenById($childStrategyId)) {
                    // ! recursion
                    $result[ $property ] = $fn($childStrategy);

                } else {
                    foreach ( $strategy->getOne() as $childProperty => $childClass ) {
                        $result[ $childProperty ] = $childClass;
                    }

                    foreach ( $strategy->getMany() as $childProperty => $childClass ) {
                        $result[ $childProperty ] = $childClass;
                    }
                }
            }

            return $result;
        } )($this->rootNode);
    }
}
