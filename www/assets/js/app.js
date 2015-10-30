$(function () {

    function showLoader() {
        $('#loader').show();
    }

    function hideLoader() {
        $('#loader').hide();
    }

    // create node
    $('#create-node-form').submit(function (e) {
        e.preventDefault();
        var labelInput = $('#create-node-label'),
            parentInput = $('#create-node-parent-id'),
            label = labelInput.val(),
            parentId = parentInput.val();

        if (label.length === 0) {
            return false;
        }

        showLoader();

        $.post('/node', {'label': label, 'parent_id': parentId})
            .success(function () {
                hideLoader();
                labelInput.val('');
                parentInput.val('');
                rebuildTree($('#tree-children-depth').val(), $('#tree-children-root-node-id').val());
            })
            .error(function (resp) {
                hideLoader();
                alert('Error');
                console.error(resp);
            });
    });

    // update node
    $('#update-node-form').submit(function (e) {
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
        showLoader();
        $.post('/node/update', {'node_id': id, 'label': label, 'parent_id': parentId})
            .success(function () {
                hideLoader();
                rebuildTree($('#tree-children-depth').val(), $('#tree-children-root-node-id').val());
            })
            .error(function (resp) {
                hideLoader();
                alert('Error');
                console.error(resp);
            });
    });

    // build tree
    $('#tree-children-form').submit(function (e) {
        e.preventDefault();
        var depth = $('#tree-children-depth').val(),
            rootNodeId = $('#tree-children-root-node-id').val();

        rebuildTree(depth, rootNodeId);
    });

    // delete node and all children
    $('#tree').on('submit', '.delete-form', function (e) {
        e.preventDefault();
        var form = $(this),
            node = form.parent(),
            id = form.find('input[type="hidden"]').val();

        showLoader();
        $.post('/node/delete', {'node_id': id})
            .success(function (resp) {
                hideLoader();
                node.remove();
            })
            .error(function (resp) {
                hideLoader();
                alert('Error');
                console.log(resp);
            })
    });

    // find nodes
    $('#node-search-form').submit(function (e) {
        e.preventDefault();
        var query = $('#node-search-query').val().trim();
        if (query.length === 0) {
            return false;
        }
        showLoader();
        $.getJSON(
            '/tree/search',
            {query: query},
            function (resp) {
                hideLoader();
                if (typeof resp !== 'object' && typeof resp !== 'array') {
                    $('#tree').html('');
                    return false;
                }
                $('#tree').html(buildTree(resp));
            }
        );
    });

    $('#generate-form').submit(function (e) {
        e.preventDefault();
        var depth = $('#generate-depth').val(),
            size = $('#generate-size').val();

        showLoader();

        $.post('/generate', {depth: depth, size: size})
            .success(function () {
                hideLoader();
                rebuildTree(1);
            })
            .error(function (resp) {
                hideLoader();
                alert('Error');
                console.error(resp);
            });
    });

    // get tree and build html
    function rebuildTree(depth, rootNodeId) {
        showLoader();
        $.getJSON(
            '/tree/children',
            {'depth': depth, 'root_node_id': rootNodeId},
            function (resp) {
                hideLoader();
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

            tree += '<div style="padding:5px;margin:5px;border:1px solid #ccc;">';
            tree += label + ' (id: ' + id + ', depth: ' + depth + ', children: ' + nodes[node]['children'].length + ')';

            // delete form
            tree += '<form class="delete-form" method="POST" action="/node/delete" style="float:right">' +
            '<input type="hidden" name="node_id" value="' + id + '">' +
            '<input type="submit" value="Delete">' +
            '</form>';

            // recursive children
            if (nodes[node]['children'].length > 0) {
                tree += '&nbsp;<a href="#" class="expand" style="text-decoration:none">+</a>';
                tree += '<div class="children" style="display:none">';
                tree += buildTree(nodes[node]['children']);
                tree += '</div>';
            }
            tree += '</div>';
        }

        return tree;
    }

    $('#tree').on('click', '.expand', function(e){
        e.preventDefault();
        var link = $(this);
        if (link.text() == '+') {
            link.text('-');
            link.siblings('.children').show();
        } else {
            link.siblings('.children').hide();
            link.text('+');
        }
    });
});