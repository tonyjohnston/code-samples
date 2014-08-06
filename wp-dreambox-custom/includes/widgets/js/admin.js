(function ($) {
	"use strict";
	$(function () {
		// Place your administration-specific JavaScript here
	});
}(jQuery));

function dreambox_term_ajax_get(termID) {
    jQuery("a.ajax").removeClass("current");
    jQuery("a.ajax").addClass("current"); //adds class current to the category menu item being displayed so you can style it with css
    jQuery("#loading-animation").show();
    var ajaxurl = '/wp-admin/admin-ajax.php';
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {"action": "dreambox-load-filter", term: termID },
        success: function(response) {
            jQuery("#category-post-content").html(response);
            jQuery("#loading-animation").hide();
            return false;
        }
    });
}