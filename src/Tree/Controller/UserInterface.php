<?php

namespace Forestry\Tree\Controller;

use Forestry\Framework\Http\Response;

class UserInterface
{
    /**
     * @return Response
     */
    public function index()
    {
        return new Response($this->generatePage());
    }

    /**
     * @return string
     */
    private function generatePage()
    {
        return '<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <title>Forestry</title>
                </head>
                <body>
                ' . $this->getControls() . '
                <div id="tree"></div>
                <script src="/assets/js/jquery.min.js"></script>
                <script>' . $this->getAppJs() . '</script>
                </body>
            </html>';
    }
    private function getControls()
    {
        return '
            <div>
                <form method="POST" action="/schema" id="create-schema-form">
                    <input type="submit" value="Create schema" id="create-schema-submit">
                </form>
            </div>
            <hr>
            <div>
                <b>Generate nodes:</b>
                <form method="POST" action="/generate" id="generate-form">
                    <label for="generate-depth">
                        Depth: <input type="text" name="depth" id="generate-depth">
                    </label>
                    <label for="generate-size">
                        Size: <input type="text" name="size" id="generate-size">
                    </label>
                    <input type="submit" value="Generate" id="generate-submit">
                </form>
            </div>
            <hr>
            <div>
                <b>Build tree:</b>
                <form method="GET" action="/tree/children" id="tree-children-form">
                    <label for="tree-children-depth">
                        Depth: <input type="text" name="depth" id="tree-children-depth">
                    </label>
                    <label for="tree-children-root-node-id">
                        Root node ID: <input type="text" name="root_node_id" id="tree-children-root-node-id">
                    </label>
                    <input type="submit" value="Build tree" id="tree-children-submit">
                </form>
            </div>
            <hr>
            <div>
                <b>Create node:</b>
                <form method="POST" action="/node" id="create-node-form">
                    <label for="create-node-label">
                        Label:
                        <input type="text" name="label" id="create-node-label">
                    </label>
                    <label for="create-node-parent-id">
                        Parent ID:
                        <input type="text" name="label" id="create-node-parent-id">
                    </label>
                    <input type="submit" value="Add" id="create-node-submit">
                </form>
            </div>
            <hr>
            <div>
                <b>Search:</b>
                <form method="GET" action="/node/search" id="node-search-form">
                    <label for="node-search-query">
                        Query: <input type="text" name="query" id="node-search-query">
                    </label>
                    <input type="submit" value="Find nodes" id="node-search-submit">
                </form>
            </div>
            <hr>
        ';
    }

    /**
     * TODO: Had to do it because nginx f*cked up my js files by adding garbage in the end
     *
     * @return string
     */
    private function getAppJs()
    {
        return file_get_contents(__DIR__ . '/../../../www/assets/js/app.js');
    }
}
