var blurFlag = false;

function glgaccolades_init()
{
    // Real browser events
    $(window).focus(function() {
	window.focus();
	blurFlag = false;
    });    
    
    // Weird IE8 event
    $(window).focusin(function() {
	blurFlag = false;
    });

    $(window).blur(function() {
	blurFlag = true;
    });    
}

function glgaccolades_timer()
{   
    //console.log("STATUS: "+document.blurFlag);
    /* TODO: Set and get timeout interval via admin interface */
    glgaccolades_refresh();
    setTimeout("glgaccolades_timer()", 3000);
}

function glgaccolades_refresh()
{    
    if(blurFlag) {
	return;
    };
    
    var glgScroller = jQuery("#glgaccolades-scroller");
    
    var firstQuote = glgScroller.find('div').filter(':visible:first');
    var newQuote = '<div class="glgaccolades-scroller">'+firstQuote.html()+'</div>';
    var height = firstQuote.height();
    
    glgScroller.append(newQuote);
    firstQuote.animate({marginTop:'-='+height},1000,'swing',function(){$("#glgaccolades-scroller").find('div').filter(':visible:first').remove();});
}


	