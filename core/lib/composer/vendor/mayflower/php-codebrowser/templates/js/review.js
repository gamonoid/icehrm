function switchLine(lineId) {
    $('#' + lineId).effect("highlight", {color: ''}, 3000);
}

function initReview() {
    $('.hasIssues').cluetip({
        positionBy: 'mouse',
        splitTitle: '|',
        activation: 'hover',
        dropShadow: false,
        tracking: true,
        cluetipClass: 'default'
    });

    $("div#sidebar").sidebar({
        width:600,
        height: 400,
        open : "click",
        position: "right"
    });

    $("div#sidebar a").click(function(event) {
        event.preventDefault();
        $(document).scrollTop($(this.hash).offset().top);
    });
}
