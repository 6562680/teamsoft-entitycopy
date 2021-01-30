<?php

namespace Teamsoft\EntityCopy;

/**
 * Class EntityCopyPipeline
 */
class EntityCopyPipeline
{
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
     * @param \Closure $pipe
     *
     * @return static
     */
    public function pipe(\Closure $pipe) : EntityCopyPipeline
    {
        $this->pipe[] = $pipe;

        return $this;
    }

    /**
     * @param \Closure $pipe
     *
     * @return static
     */
    public function pipeOne(\Closure $pipe) : EntityCopyPipeline
    {
        $this->pipeOne[] = $pipe;

        return $this;
    }

    /**
     * @param \Closure $pipe
     *
     * @return static
     */
    public function pipeMany(\Closure $pipe) : EntityCopyPipeline
    {
        $this->pipeMany[] = $pipe;

        return $this;
    }


    /**
     * @param object      $result
     * @param object      $parent
     * @param string      $property
     * @param null|string $idx
     *
     * @return object
     */
    public function handle(object $result, object $parent, string $property, string $idx = null) : object
    {
        foreach ( $this->pipe as $pipe ) {
            $result = $pipe($result, $parent, $property, $idx);
        }

        return $result;
    }

    /**
     * @param object      $result
     * @param object      $parent
     * @param string      $property
     * @param null|string $idx
     *
     * @return object
     */
    public function handleOne(object $result, object $parent, string $property, string $idx = null) : object
    {
        foreach ( $this->pipeOne as $pipe ) {
            $result = $pipe($result, $parent, $property, $idx);
        }

        return $result;
    }

    /**
     * @param iterable $results
     * @param object   $parent
     * @param string   $property
     *
     * @return object[]
     */
    public function handleMany(iterable $results, object $parent, string $property) : iterable
    {
        foreach ( $this->pipeMany as $pipe ) {
            $results = $pipe($results, $parent, $property);
        }

        return $results;
    }
}
