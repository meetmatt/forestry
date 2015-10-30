<?php

namespace Forestry\Tree\Model;

class Node
{
    /** @var int */
    private $id;
    /** @var array */
    private $path;
    /** @var string */
    private $label;
    /** @var int */
    private $depth;
    /** @var Node[] */
    private $children;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int Returns 0 if node has not parents
     */
    public function getParentId()
    {
        $path = $this->path;
        $pathLength = count($path);
        if ($pathLength === 1) {
            return 0;
        }

        return $path[$pathLength-2];
    }

    /**
     * @param array $path
     */
    public function setPath(array $path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @param Node[] $nodes
     */
    public function setChildren($nodes)
    {
        $this->children = $nodes;
    }

    /**
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }
}
