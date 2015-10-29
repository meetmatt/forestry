<?php

namespace Forestry\Tree\Model;

class NodeBuilder
{
    const ID = 'node_id';
    const PATH = 'path';
    const LABEL = 'label';
    const DEPTH = 'depth';

    /**
     * @param array $array
     * @return Node
     */
    public static function createFromArray(array $array)
    {
        $node = new Node();
        if (isset($array[self::ID])) {
            $node->setId((int)$array[self::ID]);
        }
        if (isset($array[self::LABEL])) {
            $node->setLabel($array[self::LABEL]);
        }
        if (isset($array[self::PATH])) {
            $path = $array[self::PATH];
            if (!is_array($path)) {
                $path = self::splitPath($path);
            }
            $node->setPath($path);
        }
        if (isset($array[self::DEPTH])) {
            $node->setDepth((int)$array[self::DEPTH]);
        }

        return $node;
    }

    /**
     * @param Node $node
     * @return array
     */
    public static function toArray(Node $node)
    {
        return [
            self::ID => $node->getId(),
            self::LABEL => $node->getLabel(),
            self::PATH => self::joinPath($node->getPath()),
            self::DEPTH => $node->getDepth(),
        ];
    }

    /**
     * @param string $path
     * @return array
     */
    public static function splitPath($path)
    {
        $path = str_replace(['{', '}'], '', $path);
        $path = explode(',', $path);
        $path = array_map('intval', $path);

        return $path;
    }

    /**
     * @param array $path
     * @return string
     */
    public static function joinPath(array $path)
    {
        $path = "{" . implode(',', $path) . "}";

        return $path;
    }
}
