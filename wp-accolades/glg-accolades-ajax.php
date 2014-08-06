<?php
function glgaccolades_scripts()
{
	global $glgaccolades_auto_refresh_max, $glgaccolades_next_quote, $glgaccolades_version;

	$nextquote =  $glgaccolades_next_quote?$glgaccolades_next_quote:__('Next quote', 'glg-accolades')."&nbsp;&raquo;";
	$loading = __('Loading...', 'glg-accolades');
	$error = __('Error getting quote', 'glg-accolades');
	$auto_refresh_max = $glgaccolades_auto_refresh_max;

	wp_enqueue_script( 'glgaccolades', plugin_dir_url(__FILE__).'glg-accolades.js', array('jquery'), $glgaccolades_version );
	wp_localize_script( 'glgaccolades', 'QCAjax', array(
	    // URL to wp-admin/admin-ajax.php to process the request
	    'ajaxurl' => admin_url( 'admin-ajax.php' ),
 
 	    // generate a nonce with a unique ID "myajax-post-comment-nonce"
	    // so that you can check it later when an AJAX request is sent
	    'nonce' => wp_create_nonce( 'glgaccolades' ),

	    'nextquote' => $nextquote,
	    'loading' => $loading,
	    'error' => $error,
	    'auto_refresh_max' => $glgaccolades_auto_refresh_max,
	    'auto_refresh_count' => 0
	    )
	);
}
add_action('init', 'glgaccolades_scripts');


function glgaccolades_load()
{
	check_ajax_referer('glgaccolades');	
	
	
	$show_author = isset($_POST['show_author'])?$_POST['show_author']:1;
	$show_source = isset($_POST['show_source'])?$_POST['show_source']:1;
	$auto_refresh = isset($_POST['auto_refresh'])?$_POST['auto_refresh']:0;	
	$random_refresh = isset($_POST['random_refresh'])?$_POST['random_refresh']:1;	
	$char_limit = (isset($_POST['char_limit']) && is_numeric($_POST['char_limit']))?$_POST['char_limit']:'';
	$count = isset($_POST['count']) ? $_POST['count']:0;
	
	if($random_refresh && $_POST['current'] && is_numeric($_POST['current'])) {
		$exclude = $_POST['current'];
		$current = '';
	}
	else {
		if ($_POST['current'] && is_numeric($_POST['current']))
			$current = $_POST['current'];
		$exclude = '';
	}
		
	$tags = $_POST['tags'];
	
	$args = "echo=0&ajax_refresh=2&auto_refresh={$auto_refresh}&show_author={$show_author}&show_source={$show_source}&char_limit={$char_limit}&exclude={$exclude}&tags={$tags}&random={$random_refresh}&current={$current}&count={$count}";
		

	if($response = glgaccolades_quote($args)) {
		@header("Content-type: text/html; charset=utf-8");
		die( $response ); 
	}
	else
		die( $error );
}
add_action ("wp_ajax_glgaccolades", "glgaccolades_load");
add_action ("wp_ajax_nopriv_glgaccolades", "glgaccolades_load");
?>
