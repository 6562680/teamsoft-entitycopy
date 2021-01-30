<?php

namespace Teamsoft\EntityCopy\Tests;

class Entity
{
    /**
     * Constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->title = $attributes[ 'title' ] ?? null;
        $this->same = $attributes[ 'same' ] ?? null;
        $this->copy = $attributes[ 'copy' ] ?? null;
        $this->copy2 = $attributes[ 'copy2' ] ?? null;
        $this->children = $attributes[ 'children' ] ?? null;
    }


    /**
     * @var Entity[]
     */
    protected $title;
    /**
     * @var Entity
     */
    protected $same;
    /**
     * @var Entity
     */
    protected $copy;
    /**
     * @var Entity
     */
    protected $copy2;
    /**
     * @var Entity[]
     */
    protected $children = [];
}
