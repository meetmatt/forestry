<?php

namespace Forestry\Tree\Model;

use Forestry\Framework\Database\Postgres;

/**
 * Implements tree crud operations
 *
 * TODO: prevent creates/updates that lead to cyclomatic structure
 *
 * TODO: implement a recursive tree builder (@see self::getFlat method)
 *
 */
class Tree
{
    const MAX_DEPTH = 100;
    const ANY_ROOT = -1;

    /** @var Postgres */
    private $db;

    /**
     * @param Postgres $db
     */
    public function __construct(Postgres $db)
    {
        $this->db = $db;
    }

    /**
     * Build a flat array of nodes
     *
     * @param int $depth
     * @param int $rootNodeId
     * @return array
     */
    public function getFlat($depth, $rootNodeId)
    {
        $tree = [];

        $sql = $this->getSelectQuery($depth, $rootNodeId);
        $result = $this->db->select($sql);

        foreach ($result as $row) {
            $path = $row['path'];
            if (!isset($tree[$path])) {
                $tree[$path] = [];
            }

            $node = NodeBuilder::createFromArray($row);
            $tree[$path][] = NodeBuilder::toArray($node);
        }

        return $tree;
    }

    /**
     * Find a node by ID
     *
     * @param int $id
     * @return Node
     */
    public function getNode($id)
    {
        $id = (int)$id;
        $sql = "SELECT *, (ARRAY_LENGTH(path, 1)-1) AS depth FROM hierarchy WHERE node_id = :node_id";

        return NodeBuilder::createFromArray($this->db->fetchRow($sql, ['node_id' => $id]));
    }


    /**
     * Create a node and optionally place it under a parent node
     *
     * @param string $label
     * @param null $parentNodeId
     * @return Node|bool New node or false on failure
     */
    public function createNode($label, $parentNodeId = null)
    {
        $this->db->begin();

        // get parent path
        $path = [];
        if (isset($parentNodeId) && !is_null($parentNodeId)) {
            $parentNodeId = (int)$parentNodeId;
            $sql = "SELECT path FROM hierarchy WHERE node_id = :parent_node_id";
            $parentNode = $this->db->fetchRow($sql, ['parent_node_id' => $parentNodeId]);
            if ($parentNode === false || empty($parentNode)) {
                $this->db->rollback();

                return false;
            }

            $path = NodeBuilder::splitPath($parentNode['path']);
        }

        // insert node returning new id
        $sql = "INSERT INTO hierarchy (path, label) VALUES ('{}', :label) RETURNING node_id";
        $node = $this->db->insert($sql, ['label' => $label]);

        // update nodes path
        if ($node !== false) {
            array_push($path, (int)$node['node_id']);

            $sql = "UPDATE hierarchy SET path = :path WHERE node_id = :node_id";
            $path = NodeBuilder::joinPath($path);

            if ($this->db->update($sql, ['path' => $path, 'node_id' => $node['node_id']]) !== false) {
                $this->db->commit();

                return $this->getNode($node['node_id']);
            }
        }

        $this->db->rollback();
        return false;
    }

    /**
     * Update nodes label and parent by modifying its own and all descendant paths
     *
     * @param int $id
     * @param string $label
     * @param $parentNodeId
     * @return int|bool Number of updated rows or false on failure
     */
    public function updateNode($id, $label, $parentNodeId)
    {
        $id = (int)$id;
        $this->db->begin();

        // get parent path
        $sql = "SELECT path, array_length(path,1) as depth
                FROM hierarchy
                WHERE node_id = :parent_node_id";
        $parentNode = $this->db->fetchRow($sql, ['parent_node_id' => (int)$parentNodeId]);
        if ($parentNode === false || empty($parentNode)) {
            $this->db->rollback();

            return false;
        }

        $path = NodeBuilder::splitPath($parentNode['path']);

        $node = $this->getNode($id);
        if ($node !== false) {
            // update current and all child nodes
            $sql = sprintf("UPDATE hierarchy
                            SET path = :path || path[%d:array_length(path, 1)],
                                label = :label
                            WHERE path && ARRAY[%d]", $parentNode['depth'], $id);

            $path = NodeBuilder::joinPath($path);
            $rowsUpdated = $this->db->update($sql, ['path' => $path, 'label' => $label]);
            if ($rowsUpdated !== false) {
                $this->db->commit();

                return $rowsUpdated;
            }
        }

        $this->db->rollback();
        return false;
    }

    /**
     * Remove a node with all child nodes
     *
     * @param int $nodeId
     * @return false|\PDOStatement
     */
    public function deleteNode($nodeId)
    {
        $nodeId = (int)$nodeId;

        $sql = sprintf("DELETE FROM hierarchy WHERE path && ARRAY[%d]", $nodeId);
        return $this->db->delete($sql);
    }

    /**
     * Create table
     */
    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS hierarchy (
            node_id serial PRIMARY KEY,
            path integer[] NOT NULL,
            label varchar(1000) NOT NULL
        )';

        $this->db->exec($sql);
    }

    /**
     * @param int $depth
     * @param int $rootNodeId
     * @return string
     */
    private function getSelectQuery($depth = self::MAX_DEPTH, $rootNodeId = self::ANY_ROOT)
    {
        $depth = (int)$depth;
        $rootNodeId = (int)$rootNodeId;

        $sql = "SELECT node_id, label, path, (ARRAY_LENGTH(path, 1)-1) AS depth
                FROM hierarchy
                WHERE (ARRAY_LENGTH(path, 1)-1) <= $depth
                " . ($rootNodeId !== self::ANY_ROOT ? "AND path && ARRAY[$rootNodeId]" : '') . "
                ORDER BY path ASC";

        return $sql;
    }
}
