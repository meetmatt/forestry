<?php

namespace Forestry\Tree\Controller;

use Forestry\Framework\Http\Request;
use Forestry\Framework\Http\Response;
use Forestry\Tree\Model\Generator;
use Forestry\Tree\Model\NodeBuilder;
use Forestry\Tree\Model\Tree;

class Api
{
    /** @var Tree */
    private $tree;
    /** @var Generator */
    private $generator;

    /**
     * @param Tree $tree
     * @param Generator $generator
     */
    public function __construct(Tree $tree, Generator $generator)
    {
        $this->tree = $tree;
        $this->generator = $generator;
    }

    public function createSchema()
    {
        $this->tree->createTable();
    }

    /**
     * @param Request $request
     */
    public function generate(Request $request)
    {
        $depth = (int)$request->request->offsetGet('depth', Generator::MAX_GENERATE_DEPTH);
        $size = (int)$request->request->offsetGet('size', Generator::MAX_GENERATE_SIZE);

        $this->generator->generateTree($depth, $size);
    }


    /**
     * @param Request $request
     * @return Response
     */
    public function getNode(Request $request)
    {
        $id = (int)$request->query->offsetGet('node_id');
        $node = $this->tree->getNode($id);

        if ($node === false) {
            return new Response('Node not found', Response::HTTP_NOT_FOUND);
        }

        return new Response(
            json_encode(NodeBuilder::toArray($node)),
            Response::HTTP_OK,
            Response::CONTENT_TYPE_JSON
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getChildrenTree(Request $request)
    {
        $maxDepth = (int)$request->query->offsetGet('depth', Tree::MAX_DEPTH);
        $rootNodeId = (int)$request->query->offsetGet('root_node_id', Tree::ANY_ROOT);

        $nodes = $this->tree->getChildrenTree($maxDepth, $rootNodeId);
        $tree = NodeBuilder::treeToArray($nodes);

        return new Response(
            json_encode($tree),
            Response::HTTP_OK,
            Response::CONTENT_TYPE_JSON
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getParentTree(Request $request)
    {
        $nodeId = (int)$request->query->offsetGet('node_id', Tree::MAX_DEPTH);

        $node = $this->tree->getNode($nodeId);
        if ($node === false) {
            return new Response('Node not found', Response::HTTP_NOT_FOUND);
        }

        $nodes = $this->tree->getParentTree($node);
        $tree = NodeBuilder::treeToArray($nodes);

        return new Response(
            json_encode($tree),
            Response::HTTP_OK,
            Response::CONTENT_TYPE_JSON
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getContainingTree(Request $request)
    {
        $nodeId = (int)$request->query->offsetGet('node_id', Tree::MAX_DEPTH);

        $node = $this->tree->getNode($nodeId);
        if ($node === false) {
            return new Response('Node not found', Response::HTTP_NOT_FOUND);
        }

        $nodes = $this->tree->getContainingTree($node);
        $tree = NodeBuilder::treeToArray($nodes);

        return new Response(
            json_encode($tree),
            Response::HTTP_OK,
            Response::CONTENT_TYPE_JSON
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function findNode(Request $request)
    {
        if (!$request->query->offsetExists('query')) {
            return new Response('query is required', Response::HTTP_BAD_REQUEST);
        }

        if (strlen($query = trim($request->query->offsetGet('query'))) === 0) {
            return new Response('query must not be empty', Response::HTTP_BAD_REQUEST);
        }

        $nodes = $this->tree->findNode($query);
        $trees = $this->tree->getAllParentTrees($nodes);
        $tree = NodeBuilder::treeToArray($trees);

        return new Response(
            json_encode($tree),
            Response::HTTP_OK,
            Response::CONTENT_TYPE_JSON
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createNode(Request $request)
    {
        if (!$request->request->offsetExists('label')) {
            return new Response('label is required', Response::HTTP_BAD_REQUEST);
        }

        $label = $request->request->offsetGet('label');
        $parentId = $request->request->offsetGet('parent_id');

        if (($node = $this->tree->createNode($label, $parentId)) !== false) {

            return new Response(
                json_encode(NodeBuilder::toArray($node)),
                Response::HTTP_CREATED,
                Response::CONTENT_TYPE_JSON
            );
        }

        return new Response(null, Response::HTTP_SERVER_ERROR);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function updateNode(Request $request)
    {
        if (!$request->request->offsetExists('id')) {
            return new Response('id is required', Response::HTTP_BAD_REQUEST);
        }

        if (!$request->request->offsetExists('label')) {
            return new Response('label is required', Response::HTTP_BAD_REQUEST);
        }

        if (!$request->request->offsetExists('parent_id')) {
            return new Response('parent_id is required', Response::HTTP_BAD_REQUEST);
        }

        $nodeId = (int)$request->request->offsetGet('id');
        $label = $request->request->offsetGet('label');
        $parentId = (int)$request->request->offsetGet('parent_id');

        if (($rowsUpdated = $this->tree->updateNode($nodeId, $label, $parentId)) !== false) {
            $node = $this->tree->getNode($nodeId);

            return new Response(
                json_encode(NodeBuilder::toArray($node)),
                Response::HTTP_OK,
                Response::CONTENT_TYPE_JSON
            );
        }

        return new Response(null, Response::HTTP_SERVER_ERROR);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function deleteNode(Request $request)
    {
        if (!$request->request->offsetExists('node_id')) {
            return new Response('node_id is required', Response::HTTP_BAD_REQUEST);
        }

        $nodeId = (int)$request->request->offsetGet('node_id');
        $node = $this->tree->getNode($nodeId);
        if ($node === false) {
            return new Response('Node not found', Response::HTTP_NOT_FOUND);
        }

        if ($this->tree->deleteNode($node)) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return new Response(null, Response::HTTP_SERVER_ERROR);
    }
}
