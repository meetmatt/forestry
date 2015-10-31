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
    const DEFAULT_DEPTH = 10;

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
     * Build a flat array of nodes deep down
     *
     * @param int $depth
     * @param int $rootNodeId
     * @return array
     */
    public function getChildrenTree($depth, $rootNodeId)
    {
        $sql = $this->getChildrenSelectQuery($depth, $rootNodeId);
        $result = $this->db->select($sql);

        $nodes = [];
        foreach ($result as $row) {
            $nodes[] = NodeBuilder::createFromArray($row);
        }

        $tree = $this->buildTree($nodes);

        return $tree;
    }

    /**
     * Build a flat array of nodes from some node up to root
     *
     * @param Node $node
     * @return array
     */
    public function getParentTree($node)
    {
        $sql = $this->getParentSelectQuery($node);
        $result = $this->db->select($sql);

        $nodes = [];
        foreach ($result as $row) {
            $nodes[] = NodeBuilder::createFromArray($row);
        }

        $tree = $this->buildTree($nodes);

        return $tree;
    }

    /**
     * Build a flat array of nodes from several nodes up to all roots
     *
     * @param Node[] $nodes
     * @return array
     */
    public function getAllParentTrees(array $nodes)
    {
        $sql = $this->getAllParentsSelectQuery($nodes);
        $result = $this->db->select($sql);

        $nodes = [];
        foreach ($result as $row) {
            $nodes[] = NodeBuilder::createFromArray($row);
        }

        $tree = $this->buildTree($nodes);

        return $tree;
    }

    /**
     * Build a flat array of nodes from some node up to root
     *
     * @param Node $node
     * @return array
     */
    public function getContainingTree($node)
    {
        $sql = $this->getContainingTreeQuery($node);
        $result = $this->db->select($sql);

        $nodes = [];
        foreach ($result as $row) {
            $nodes[] = NodeBuilder::createFromArray($row);
        }

        $tree = $this->buildTree($nodes);

        return $tree;
    }

    /**
     * @param Node[] $nodes
     * @param int $parentId
     * @return array
     */
    private function buildTree(array &$nodes, $parentId = 0) {

        $branch = [];

        foreach ($nodes as &$node) {
            $id = $node->getId();
            $nodeParentId = $node->getParentId();

            if ($nodeParentId === $parentId) {
                $children = $this->buildTree($nodes, $id);
                if ($children) {
                    $node->setChildren($children);
                }

                $branch[$id] = $node;
                unset($node);
            }
        }

        return $branch;
    }

    /**
     * @param Node $node
     * @return string
     */
    private function getParentSelectQuery(Node $node)
    {
        $path = $node->getPath();
        $sql = "SELECT node_id, label, path, ARRAY_LENGTH(path, 1) AS depth
                FROM hierarchy
                WHERE ARRAY[node_id] && ARRAY[%s]
                ORDER BY path ASC";

        $sql = sprintf($sql, implode(',', $path));

        return $sql;
    }

    /**
     * Generates a select query to get all parent nodes which have stated nodes
     *
     * @param Node[] $nodes
     * @return string
     */
    private function getAllParentsSelectQuery(array $nodes)
    {
        $sql = "SELECT node_id, label, path, ARRAY_LENGTH(path, 1) AS depth
                FROM hierarchy
                WHERE 1=0";

        foreach ($nodes as $node) {
            $sql .= sprintf("
                OR (ARRAY[node_id] && ARRAY[%s])", implode(',', $node->getPath()));
        }

        $sql .= "
                ORDER BY path ASC";

        return $sql;
    }

    /**
     * @param int $depth
     * @param int $rootNodeId
     * @return string
     */
    private function getChildrenSelectQuery($depth = self::MAX_DEPTH, $rootNodeId = self::ANY_ROOT)
    {
        $depth = (int)$depth;
        $rootNodeId = (int)$rootNodeId;

        $sql = "SELECT node_id, label, path, ARRAY_LENGTH(path, 1) AS depth
                FROM hierarchy
                WHERE ARRAY_LENGTH(path, 1) <= $depth
                " . ($rootNodeId !== self::ANY_ROOT ? "AND path && ARRAY[$rootNodeId]" : '') . "
                ORDER BY path ASC";

        return $sql;
    }

    /**
     * @param Node $node
     * @return string
     */
    private function getContainingTreeQuery(Node $node)
    {
        $path = $node->getPath();
        $id = (int)$node->getId();

        $sql = "SELECT node_id, label, path, ARRAY_LENGTH(path, 1) AS depth
                FROM hierarchy
                WHERE (ARRAY[node_id] && ARRAY[%s])
                   OR (path && ARRAY[%d])
                ORDER BY path ASC";

        $sql = sprintf($sql, implode(',', $path), $id);

        return $sql;
    }

    /**
     * @param string $query
     * @return Node[]
     */
    public function findNode($query)
    {
        $sql = "SELECT node_id, label, path, ARRAY_LENGTH(path, 1) AS depth
                FROM hierarchy
                WHERE label LIKE :query";
        $nodes = $this->db->select($sql, ['query' => '%' . $query . '%']);
        $result = [];
        if (count($nodes)) {
            foreach ($nodes as $node) {
                $result[] = NodeBuilder::createFromArray($node);
            }
        }

        return $result;
    }

    /**
     * Find a node by ID
     *
     * @param int $id
     * @return Node|bool Node or false on failure
     */
    public function getNode($id)
    {
        $id = (int)$id;
        $sql = "SELECT node_id, label, path, ARRAY_LENGTH(path, 1) AS depth
                FROM hierarchy
                WHERE node_id = :node_id
                LIMIT 1";

        $nodes = $this->db->select($sql, ['node_id' => $id]);
        if (count($nodes)) {
            return NodeBuilder::createFromArray($nodes[0]);
        }

        return false;
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
            $sql = "SELECT path
                    FROM hierarchy
                    WHERE node_id = :parent_node_id";
            $nodes = $this->db->select($sql, ['parent_node_id' => $parentNodeId]);
            if (count($nodes) < 1) {
                $this->db->rollback();
                return false;
            }

            $parentNode = $nodes[0];
            $path = NodeBuilder::splitPath($parentNode['path']);
        }

        // insert node returning new id
        $sql = "INSERT INTO hierarchy (path, label)
                VALUES ('{}', :label)
                RETURNING node_id";
        $node = $this->db->insert($sql, ['label' => $label]);

        // update nodes path
        if ($node !== false) {
            array_push($path, (int)$node['node_id']);

            $sql = "UPDATE hierarchy
                    SET path = :path
                    WHERE node_id = :node_id";
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
     * @param int $parentNodeId
     * @return int|bool Number of updated rows or false on failure
     */
    public function updateNode($id, $label, $parentNodeId = null)
    {
        $id = (int)$id;
        $this->db->begin();

        $path = [];
        if (isset($parentNodeId) && !is_null($parentNodeId)) {
            // get parent path
            $sql = "SELECT path, array_length(path,1) AS depth
                    FROM hierarchy
                    WHERE node_id = :parent_node_id";
            $nodes = $this->db->select($sql, ['parent_node_id' => (int)$parentNodeId]);
            if (count($nodes) < 1) {
                // parent not found
                $this->db->rollback();
                return false;
            }
            $parentNode = $nodes[0];
            $path = NodeBuilder::splitPath($parentNode['path']);
            $parentDepth = $parentNode['depth'];
        } else {
            // move to root
            $parentDepth = 0;
        }

        $node = $this->getNode($id);
        if ($node !== false) {
            $sql = "UPDATE hierarchy
                    SET label = :lbl
                    WHERE node_id = :id";
            $this->db->update($sql, ['lbl' => $label, 'id' => $node->getId()]);

            // update current and all child nodes path
            $sql = sprintf("UPDATE hierarchy
                            SET path = :path || path[%d:array_length(path, 1)]
                            WHERE path && ARRAY[%d]", $parentDepth, $id);

            $path = NodeBuilder::joinPath($path);
            $rowsUpdated = $this->db->update($sql, ['path' => $path]);
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
     * @param Node $node
     * @return false|\PDOStatement
     */
    public function deleteNode(Node $node)
    {
        $nodeId = $node->getId();
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
}
