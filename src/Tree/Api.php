<?php

namespace Forestry\Tree;

use Forestry\Framework\Http\Request;
use Forestry\Framework\Http\Response;
use Forestry\Tree\Model\NodeBuilder;
use Forestry\Tree\Model\Tree;

class Api
{
    /** @var Tree */
    private $tree;

    /**
     * @param Tree $tree
     */
    public function __construct(Tree $tree)
    {
        $this->tree = $tree;
    }

    public function createSchema()
    {
        $this->tree->createTable();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getNode(Request $request)
    {
        $id = (int)$request->query->offsetGet('id');
        $node = $this->tree->getNode($id);

        if ($node === false) {
            return new Response('Node not found', Response::HTTP_NOT_FOUND);
        }

        return new Response(json_encode($node));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getFlat(Request $request)
    {
        $maxDepth = (int)$request->query->offsetGet('max_depth', Tree::MAX_DEPTH);
        $rootNodeId = (int)$request->query->offsetGet('root', Tree::ANY_ROOT);

        $tree = $this->tree->getFlat($maxDepth, $rootNodeId);

        return new Response(json_encode($tree));
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

        if ($id = $this->tree->createNode($label, $parentId)) {
            $node = $this->tree->getNode($id);

            return new Response(json_encode(NodeBuilder::toArray($node)), Response::HTTP_CREATED);
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

            return new Response(json_encode(NodeBuilder::toArray($node)), Response::HTTP_OK);
        }

        return new Response(null, Response::HTTP_SERVER_ERROR);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function deleteNode(Request $request)
    {
        if (!$request->query->offsetExists('id')) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $nodeId = (int)$request->request->offsetGet('id');
        if (($rows = $this->tree->deleteNode($nodeId)) > 0) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return new Response(null, Response::HTTP_SERVER_ERROR);
    }
}
