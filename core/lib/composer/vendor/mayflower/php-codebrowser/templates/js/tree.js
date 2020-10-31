function linkClickedFunction(event) {
    event.preventDefault();
    target = $(this).attr('href');
    $.History.go(target);
}

// detect if the browser decodes hash values
//
// eg Firfox automaticly decodes hash values:
// https://bugzilla.mozilla.org/show_bug.cgi?id=483304
// 
// take solution to emulate the behaviour form here:
// http://stackoverflow.com/questions/3213531/creating-a-new-location-object-in-javascript
function browserDecodesHash() {
    var testHash = "#%20",
        url = document.createElement("a");
    url.href = testHash;
    return url.hash !== testHash;
}

$.History.bind(function (state) {
    $('.sidebar-container-right').remove();
    $('#cluetip').remove();
    $('#cluetip-waitimage').remove();

    if (state == '' || state.match('/index.html$') == '/index.html') {
        $('#contentBox').html('<h1>Loading...</h1>').load('index.html' + ' #fileList', function() {
            $('#fileList .fileLink').click(linkClickedFunction);
        });
    } else {
        // check if we have to reencode the url
        if (browserDecodesHash()) {
            state = encodeURI(state);
        }
        // Go to specific review
        $('#contentBox').empty().load(state + ' #review', initReview);
    }
});

$(function() {
    $("#treeToggle").click().toggle(function() {
        $("#tree").animate({width: "hide", opacity: "hide"}, "slow");
        $("#treeToggle").css('background-image', "url('img/treeToggle-collapsed.png')");
    }, function() {
        $("#tree").animate({width: "show", opacity: "show"}, "slow");
        $("#treeToggle").css('background-image', "url('img/treeToggle-extended.png')");
    });

    $("#tree").bind("loaded.jstree", function(event, data) {
        $("#tree").animate({width: "show", opacity: "show"}, "slow");
    }).jstree({
        "plugins" : ["html_data", "themes"]
    });

    $(".treeDir").click(function() {
        $("#tree").jstree("toggle_node", this);
    });

    // When the user clicks on a leaf item in the tree (representing a file)
    // or an item in the fileList, want to hide the filelist/the currently
    // shown review and display the correct review.
    $(".fileLink").click(linkClickedFunction);
});

