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
    protected $treeRoot;
    /**
     * @var Strategy[][]
     */
    protected $tree = [];
    /**
     * @var Strategy[]
     */
    protected $list = [];


    /**
     * @return Strategy
     */
    public function getTreeRoot() : Strategy
    {
        return $this->treeRoot;
    }

    /**
     * @return Strategy[][]
     */
    public function getTree() : array
    {
        return $this->tree;
    }

    /**
     * @return Strategy[]
     */
    public function getList() : array
    {
        return $this->list;
    }


    /**
     * @param Strategy $root
     *
     * @return StrategyTree
     */
    public function setRoot(Strategy $root) : StrategyTree
    {
        $this->treeRoot = $root;

        $this->list[ $id = $root->getId() ] = $root;

        return $this;
    }


    /**
     * @param Strategy $node
     * @param string   $property
     *
     * @return StrategyTree
     */
    public function addNode(Strategy $node, string $property) : StrategyTree
    {
        $this->addNodeFor($this->treeRoot, $node, $property);

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
        $this->tree[ $parent->getId() ][ $property ] = $node;
        $this->list[ $id = $node->getId() ] = $node;

        return $this;
    }
}
