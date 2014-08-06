var blurFlag = false;

function scrolling_accolades_init() {
    // Real browser events
    $(window).focus(function () {
        window.focus();
        blurFlag = false;
    });

    // Weird IE8 event
    $(window).focusin(function () {
        blurFlag = false;
    });

    $(window).blur(function () {
        blurFlag = true;
    });
}

function scrolling_accolades_timer() {
    //console.log("STATUS: "+document.blurFlag);
    /* TODO: Set and get timeout interval via admin interface */
    scrolling_accolades_refresh();
    setTimeout("scrolling_accolades_timer()", 3000);
}

function scrolling_accolades_refresh() {
    if (blurFlag) {
        return;
    }
    ;

    var scrollingScroller = jQuery("#scrolling_accolades-scroller");

    var firstQuote = scrollingScroller.find('div').filter(':visible:first');
    var newQuote = '<div class="scrolling_accolades-scroller">' + firstQuote.html() + '</div>';
    var height = firstQuote.height();

    scrollingScroller.append(newQuote);
    firstQuote.animate({marginTop: '-=' + height}, 1000, 'swing', function () {
        $("#scrolling_accolades-scroller").find('div').filter(':visible:first').remove();
    });
}


	