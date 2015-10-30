$(function(){

    // create node
    $('#create-node-form').submit(function(e){
        e.preventDefault();
        var labelInput = $('#create-node-label'),
            parentInput = $('#create-node-parent-id'),
            label = labelInput.val(),
            parentId = parentInput.val();

        if (label.length === 0) {
            return false;
        }

        $.post('/node', {'label': label, 'parent_id':parentId})
            .success(function(){
                labelInput.val('');
                parentInput.val('');
                rebuildTree($('#tree-children-depth').val(), $('#tree-children-root-node-id').val());
            })
            .error(function(resp){
                alert('Error');
                console.error(resp);
            });
    });

    // update node
    $('#update-node-form').submit(function(e){
        e.preventDefault();
        var idInput = $('#update-node-id'),
            labelInput = $('#update-node-label'),
            parentInput = $('#update-node-parent-id'),
            id = idInput.val(),
            label = labelInput.val(),
            parentId = parentInput.val();

        if (label.length === 0) {
            return false;
        }

        $.post('/node/update', {'node_id':id, 'label': label, 'parent_id':parentId})
            .success(function(){
                rebuildTree($('#tree-children-depth').val(), $('#tree-children-root-node-id').val());
            })
            .error(function(resp){
                alert('Error');
                console.error(resp);
            });
    });

    // build tree
    $('#tree-children-form').submit(function(e){
        e.preventDefault();
        var depth = $('#tree-children-depth').val(),
            rootNodeId = $('#tree-children-root-node-id').val();

        rebuildTree(depth, rootNodeId);
    });

    // delete node and all children
    $('#tree').on('submit', '.delete-form', function(e){
        e.preventDefault();
        var form = $(this),
            node = form.parent(),
            id = form.find('input[type="hidden"]').val();
        $.post('/node/delete', {'node_id':id})
            .success(function(resp){
                node.remove();
            })
            .error(function(resp){
                alert('Error');
                console.log(resp);
            })
    });

    // find nodes
    $('#node-search-form').submit(function(e){
        e.preventDefault();
        var query = $('#node-search-query').val().trim();
        if (query.length === 0) {
            return false;
        }
        $.getJSON(
            '/tree/search',
            {query:query},
            function(resp){
                if (typeof resp !== 'object' && typeof resp !== 'array') {
                    $('#tree').html('');
                    return false;
                }
                $('#tree').html(buildTree(resp));
            }
        );
    });

    $('#generate-form').submit(function(e){
        e.preventDefault();
        var depth = $('#generate-depth').val(),
            size = $('#generate-size').val();

        $.post('/generate', {depth:depth, size:size})
            .success(function(){
                rebuildTree(1);
            })
            .error(function(resp){
                alert('Error');
                console.error(resp);
            });
    });

    // get tree and build html
    function rebuildTree(depth, rootNodeId) {
        $.getJSON(
            '/tree/children',
            {'depth':depth, 'root_node_id':rootNodeId},
            function(resp){
                if (typeof resp !== 'object' && typeof resp !== 'array') {
                    alert('Nothing to build');
                    $('#tree').html('');
                    return false;
                }
                $('#tree').html(buildTree(resp));
            }
        );
    }

    // build html tree
    function buildTree(nodes) {
        var tree = '';
        for (var node in nodes) {
            if (!nodes.hasOwnProperty(node)) {
                continue;
            }
            var n = nodes[node],
                id = n['node_id'],
                label = n['label'],
                depth = n['depth'];

            tree += '<div style="padding:5px;margin:5px;border:1px solid #ccc; display:' + (depth > 1 ? 'hidden' : 'block') +'">';
            tree += label + ' (id: ' + id + ', depth: ' + depth + ')';

            // delete form
            tree += '<form class="delete-form" method="POST" action="/node/delete" style="float:right">' +
                        '<input type="hidden" name="node_id" value="' + id + '">' +
                        '<input type="submit" value="Delete">' +
                    '</form>';

            // recursive children
            if (nodes[node]['children'].length > 0) {
                tree += buildTree(nodes[node]['children'])
            }
            tree += '</div>';
        }

        return tree;
    }
});