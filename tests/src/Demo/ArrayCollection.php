<?php

namespace Teamsoft\EntityCopy\Tests\Demo;

/**
 * Class ArrayCollection
 */
class ArrayCollection implements \Iterator
{
    /**
     * @var array
     */
    protected $list;


    /**
     * Constructor
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->list = $elements;
    }


    /**
     * @return array
     */
    public function getList() : array
    {
        return $this->list;
    }


    /**
     * @param $item
     *
     * @return ArrayCollection
     */
    public function add($item) : ArrayCollection
    {
        $this->list[] = $item;

        return $this;
    }

    /**
     * @param string $idx
     * @param        $item
     *
     * @return ArrayCollection
     */
    public function put(string $idx, $item) : ArrayCollection
    {
        $this->list[ $idx ] = $item;

        return $this;
    }


    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->list);
    }

    /**
     * @return mixed|void
     */
    public function next()
    {
        return next($this->list);
    }

    /**
     * @return null|bool|float|int|string
     */
    public function key()
    {
        return key($this->list);
    }

    /**
     * @return bool
     */
    public function valid() : bool
    {
        return null !== $this->key();
    }

    /**
     * @return mixed|void
     */
    public function rewind()
    {
        return reset($this->list);
    }
}
