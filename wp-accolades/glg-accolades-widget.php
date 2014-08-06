<?php

function glgaccolades_widget_init()
{
	if(function_exists('load_plugin_textdomain'))
		load_plugin_textdomain('glg-accolades', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
	
	function glgaccolades_widget($args) {
		$options = get_option('glgaccolades');
		$title = isset($options['title'])?apply_filters('the_title', $options['title']):__('Random Quote', 'glg-accolades');
		$show_author = isset($options['show_author'])?$options['show_author']:1;
		$show_source = isset($options['show_source'])?$options['show_source']:1;
		$ajax_refresh = isset($options['ajax_refresh'])?$options['ajax_refresh']:1;
		$auto_refresh = isset($options['auto_refresh'])?$options['auto_refresh']:0;
		$random_refresh = isset($options['random_refresh'])?$options['random_refresh']:1;
		if($auto_refresh)
			$auto_refresh = isset($options['refresh_interval'])?$options['refresh_interval']:5;
		$char_limit = $options['char_limit'];
		$tags = $options['tags'];
		$parms = "echo=0&show_author={$show_author}&show_source={$show_source}&ajax_refresh={$ajax_refresh}&auto_refresh={$auto_refresh}&char_limit={$char_limit}&tags={$tags}&random={$random_refresh}";
		if($random_quote = glgaccolades_quote($parms)) {
			extract($args);
			echo $before_widget;
			if($title) echo $before_title . $title . $after_title . "\n";
			echo $random_quote;
			echo $after_widget;
		}
	}
	
	function glgaccolades_widget_control()
	{
		
		// default values for options
		$options = array(
			'title' => __('Random Quote', 'glg-accolades'), 
			'show_author' => 1,
			'show_source' => 0, 
			'ajax_refresh' => 1,
			'auto_refresh' => 0,
			'random_refresh' => 1,
			'refresh_interval' => 5,
			'tags' => '',
			'char_limit' => 500
		);

		if($options_saved = get_option('glgaccolades'))
			$options = array_merge($options, $options_saved);
			
		// Update options in db when user updates options in the widget page
		if(isset($_REQUEST['glgaccolades-submit']) && $_REQUEST['glgaccolades-submit']) { 
			$options['title'] 
				= strip_tags(stripslashes($_REQUEST['glgaccolades-title']));
			$options['show_author'] = (isset($_REQUEST['glgaccolades-show_author']) && $_REQUEST['glgaccolades-show_author'])?1:0;
			$options['show_source'] = (isset($_REQUEST['glgaccolades-show_source']) && $_REQUEST['glgaccolades-show_source'])?1:0;
			$options['ajax_refresh'] = (isset($_REQUEST['glgaccolades-ajax_refresh']) && $_REQUEST['glgaccolades-ajax_refresh'])?1:0;
			$options['auto_refresh'] = (isset($_REQUEST['glgaccolades-auto_refresh']) && $_REQUEST['glgaccolades-auto_refresh'])?1:0;
			$options['refresh_interval'] = $_REQUEST['glgaccolades-refresh_interval'];
			$options['random_refresh'] = (isset($_REQUEST['glgaccolades-random_refresh']) && $_REQUEST['glgaccolades-random_refresh'])?1:0;
			$options['tags'] = strip_tags(stripslashes($_REQUEST['glgaccolades-tags']));
			$options['char_limit'] = strip_tags(stripslashes($_REQUEST['glgaccolades-char_limit']));
			if(!$options['char_limit'])
				$options['char_limit'] = __('none', 'glg-accolades');
			update_option('glgaccolades', $options);
		}

		// Now we define the display of widget options menu
		$show_author_checked = $show_source_checked	= $ajax_refresh_checked = $auto_refresh_checked = $random_refresh_checked = '';
		$int_select = array ( '5' => '', '10' => '', '15' => '', '20' => '', '30' => '', '60' => '');
        if($options['show_author'])
        	$show_author_checked = ' checked="checked"';
        if($options['show_source'])
        	$show_source_checked = ' checked="checked"';
        if($options['ajax_refresh'])
        	$ajax_refresh_checked = ' checked="checked"';
        if($options['auto_refresh'])
        	$auto_refresh_checked = ' checked="checked"';
        if($options['random_refresh'])
        	$random_refresh_checked = ' checked="checked"';
        $int_select[$options['refresh_interval']] = ' selected="selected"';

		echo "<p style=\"text-align:left;\"><label for=\"glgaccolades-title\">".__('Title', 'glg-accolades')." </label><input class=\"widefat\" type=\"text\" id=\"glgaccolades-title\" name=\"glgaccolades-title\" value=\"".htmlspecialchars($options['title'], ENT_QUOTES)."\" /></p>";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"glgaccolades-show_author\" name=\"glgaccolades-show_author\" value=\"1\"{$show_author_checked} /> <label for=\"glgaccolades-show_author\">".__('Show author?', 'glg-accolades')."</label></p>";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"glgaccolades-show_source\" name=\"glgaccolades-show_source\" value=\"1\"{$show_source_checked} /> <label for=\"glgaccolades-show_source\">".__('Show source?', 'glg-accolades')."</label></p>";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"glgaccolades-ajax_refresh\" name=\"glgaccolades-ajax_refresh\" value=\"1\"{$ajax_refresh_checked} /> <label for=\"glgaccolades-ajax_refresh\">".__('Ajax refresh feature', 'glg-accolades')."</label></p>";
		echo "<p style=\"text-align:left;\"><small><a id=\"glgaccolades-adv_key\" style=\"cursor:pointer;\" onclick=\"jQuery('div#glgaccolades-adv_opts').slideToggle();\">".__('Advanced options', 'glg-accolades')." &raquo;</a></small></p>";
		echo "<div id=\"glgaccolades-adv_opts\" style=\"display:none\">";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"glgaccolades-random_refresh\" name=\"glgaccolades-random_refresh\" value=\"1\"{$random_refresh_checked} /> <label for=\"glgaccolades-random_refresh\">".__('Random refresh', 'glg-accolades')."</label><br/><span class=\"setting-description\"><small>".__('Unchecking this will rotate quotes in the order added, latest first.', 'glg-accolades')."</small></span></p>";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"glgaccolades-auto_refresh\" name=\"glgaccolades-auto_refresh\" value=\"1\"{$auto_refresh_checked} /> <label for=\"glgaccolades-auto_refresh\">".__('Auto refresh', 'glg-accolades')."</label> <label for=\"glgaccolades-refresh_interval\">".__('every', 'glg-accolades')."</label> <select id=\"glgaccolades-refresh_interval\" name=\"glgaccolades-refresh_interval\"><option{$int_select['5']}>5</option><option{$int_select['10']}>10</option><option{$int_select['15']}>15</option><option{$int_select['20']}>20</option><option{$int_select['30']}>30</option><option{$int_select['60']}>60</option></select> ".__('sec', 'glg-accolades')."</p>";
		echo "<p style=\"text-align:left;\"><label for=\"glgaccolades-tags\">".__('Tags filter', 'glg-accolades')." </label><input class=\"widefat\" type=\"text\" id=\"glgaccolades-tags\" name=\"glgaccolades-tags\" value=\"".htmlspecialchars($options['tags'], ENT_QUOTES)."\" /><br/><span class=\"setting-description\"><small>".__('Comma separated', 'glg-accolades')."</small></span></p>";
		echo "<p style=\"text-align:left;\"><label for=\"glgaccolades-char_limit\">".__('Character limit', 'glg-accolades')." </label><input class=\"widefat\" type=\"text\" id=\"glgaccolades-char_limit\" name=\"glgaccolades-char_limit\" value=\"".htmlspecialchars($options['char_limit'], ENT_QUOTES)."\" /></p>";
		echo "</div>";
		echo "<input type=\"hidden\" id=\"glgaccolades-submit\" name=\"glgaccolades-submit\" value=\"1\" />";
	}

	if ( function_exists( 'wp_register_sidebar_widget' ) ) {
		wp_register_sidebar_widget( 'glgaccolades', 'Random Quote', 'glgaccolades_widget' );
		wp_register_widget_control( 'glgaccolades', 'Random Quote', 'glgaccolades_widget_control' );
	} else {
		register_sidebar_widget(array('Random Quote', 'widgets'), 'glgaccolades_widget');
		register_widget_control('Random Quote', 'glgaccolades_widget_control', 250, 350);
	}
}

add_action('plugins_loaded', 'glgaccolades_widget_init');
?>
