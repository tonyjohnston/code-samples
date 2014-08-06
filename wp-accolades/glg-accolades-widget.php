<?php

function scrolling_accolades_widget_init()
{
    if (function_exists('load_plugin_textdomain'))
        load_plugin_textdomain('scrolling-accolades', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control'))
        return;

    function scrolling_accolades_widget($args)
    {
        $options = get_option('scrolling_accolades');
        $title = isset($options['title']) ? apply_filters('the_title', $options['title']) : __('Random Quote', 'scrolling-accolades');
        $show_author = isset($options['show_author']) ? $options['show_author'] : 1;
        $show_source = isset($options['show_source']) ? $options['show_source'] : 1;
        $ajax_refresh = isset($options['ajax_refresh']) ? $options['ajax_refresh'] : 1;
        $auto_refresh = isset($options['auto_refresh']) ? $options['auto_refresh'] : 0;
        $random_refresh = isset($options['random_refresh']) ? $options['random_refresh'] : 1;
        if ($auto_refresh)
            $auto_refresh = isset($options['refresh_interval']) ? $options['refresh_interval'] : 5;
        $char_limit = $options['char_limit'];
        $tags = $options['tags'];
        $parms = "echo=0&show_author={$show_author}&show_source={$show_source}&ajax_refresh={$ajax_refresh}&auto_refresh={$auto_refresh}&char_limit={$char_limit}&tags={$tags}&random={$random_refresh}";
        if ($random_quote = scrolling_accolades_quote($parms)) {
            extract($args);
            echo $before_widget;
            if ($title) echo $before_title . $title . $after_title . "\n";
            echo $random_quote;
            echo $after_widget;
        }
    }

    function scrolling_accolades_widget_control()
    {

        // default values for options
        $options = array(
            'title' => __('Random Quote', 'scrolling-accolades'),
            'show_author' => 1,
            'show_source' => 0,
            'ajax_refresh' => 1,
            'auto_refresh' => 0,
            'random_refresh' => 1,
            'refresh_interval' => 5,
            'tags' => '',
            'char_limit' => 500
        );

        if ($options_saved = get_option('scrolling_accolades'))
            $options = array_merge($options, $options_saved);

        // Update options in db when user updates options in the widget page
        if (isset($_REQUEST['scrolling_accolades-submit']) && $_REQUEST['scrolling_accolades-submit']) {
            $options['title']
                = strip_tags(stripslashes($_REQUEST['scrolling_accolades-title']));
            $options['show_author'] = (isset($_REQUEST['scrolling_accolades-show_author']) && $_REQUEST['scrolling_accolades-show_author']) ? 1 : 0;
            $options['show_source'] = (isset($_REQUEST['scrolling_accolades-show_source']) && $_REQUEST['scrolling_accolades-show_source']) ? 1 : 0;
            $options['ajax_refresh'] = (isset($_REQUEST['scrolling_accolades-ajax_refresh']) && $_REQUEST['scrolling_accolades-ajax_refresh']) ? 1 : 0;
            $options['auto_refresh'] = (isset($_REQUEST['scrolling_accolades-auto_refresh']) && $_REQUEST['scrolling_accolades-auto_refresh']) ? 1 : 0;
            $options['refresh_interval'] = $_REQUEST['scrolling_accolades-refresh_interval'];
            $options['random_refresh'] = (isset($_REQUEST['scrolling_accolades-random_refresh']) && $_REQUEST['scrolling_accolades-random_refresh']) ? 1 : 0;
            $options['tags'] = strip_tags(stripslashes($_REQUEST['scrolling_accolades-tags']));
            $options['char_limit'] = strip_tags(stripslashes($_REQUEST['scrolling_accolades-char_limit']));
            if (!$options['char_limit'])
                $options['char_limit'] = __('none', 'scrolling-accolades');
            update_option('scrolling_accolades', $options);
        }

        // Now we define the display of widget options menu
        $show_author_checked = $show_source_checked = $ajax_refresh_checked = $auto_refresh_checked = $random_refresh_checked = '';
        $int_select = array('5' => '', '10' => '', '15' => '', '20' => '', '30' => '', '60' => '');
        if ($options['show_author'])
            $show_author_checked = ' checked="checked"';
        if ($options['show_source'])
            $show_source_checked = ' checked="checked"';
        if ($options['ajax_refresh'])
            $ajax_refresh_checked = ' checked="checked"';
        if ($options['auto_refresh'])
            $auto_refresh_checked = ' checked="checked"';
        if ($options['random_refresh'])
            $random_refresh_checked = ' checked="checked"';
        $int_select[$options['refresh_interval']] = ' selected="selected"';

        echo "<p style=\"text-align:left;\"><label for=\"scrolling_accolades-title\">" . __('Title', 'scrolling-accolades') . " </label><input class=\"widefat\" type=\"text\" id=\"scrolling_accolades-title\" name=\"scrolling_accolades-title\" value=\"" . htmlspecialchars($options['title'], ENT_QUOTES) . "\" /></p>";
        echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"scrolling_accolades-show_author\" name=\"scrolling_accolades-show_author\" value=\"1\"{$show_author_checked} /> <label for=\"scrolling_accolades-show_author\">" . __('Show author?', 'scrolling-accolades') . "</label></p>";
        echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"scrolling_accolades-show_source\" name=\"scrolling_accolades-show_source\" value=\"1\"{$show_source_checked} /> <label for=\"scrolling_accolades-show_source\">" . __('Show source?', 'scrolling-accolades') . "</label></p>";
        echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"scrolling_accolades-ajax_refresh\" name=\"scrolling_accolades-ajax_refresh\" value=\"1\"{$ajax_refresh_checked} /> <label for=\"scrolling_accolades-ajax_refresh\">" . __('Ajax refresh feature', 'scrolling-accolades') . "</label></p>";
        echo "<p style=\"text-align:left;\"><small><a id=\"scrolling_accolades-adv_key\" style=\"cursor:pointer;\" onclick=\"jQuery('div#scrolling_accolades-adv_opts').slideToggle();\">" . __('Advanced options', 'scrolling-accolades') . " &raquo;</a></small></p>";
        echo "<div id=\"scrolling_accolades-adv_opts\" style=\"display:none\">";
        echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"scrolling_accolades-random_refresh\" name=\"scrolling_accolades-random_refresh\" value=\"1\"{$random_refresh_checked} /> <label for=\"scrolling_accolades-random_refresh\">" . __('Random refresh', 'scrolling-accolades') . "</label><br/><span class=\"setting-description\"><small>" . __('Unchecking this will rotate quotes in the order added, latest first.', 'scrolling-accolades') . "</small></span></p>";
        echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"scrolling_accolades-auto_refresh\" name=\"scrolling_accolades-auto_refresh\" value=\"1\"{$auto_refresh_checked} /> <label for=\"scrolling_accolades-auto_refresh\">" . __('Auto refresh', 'scrolling-accolades') . "</label> <label for=\"scrolling_accolades-refresh_interval\">" . __('every', 'scrolling-accolades') . "</label> <select id=\"scrolling_accolades-refresh_interval\" name=\"scrolling_accolades-refresh_interval\"><option{$int_select['5']}>5</option><option{$int_select['10']}>10</option><option{$int_select['15']}>15</option><option{$int_select['20']}>20</option><option{$int_select['30']}>30</option><option{$int_select['60']}>60</option></select> " . __('sec', 'scrolling-accolades') . "</p>";
        echo "<p style=\"text-align:left;\"><label for=\"scrolling_accolades-tags\">" . __('Tags filter', 'scrolling-accolades') . " </label><input class=\"widefat\" type=\"text\" id=\"scrolling_accolades-tags\" name=\"scrolling_accolades-tags\" value=\"" . htmlspecialchars($options['tags'], ENT_QUOTES) . "\" /><br/><span class=\"setting-description\"><small>" . __('Comma separated', 'scrolling-accolades') . "</small></span></p>";
        echo "<p style=\"text-align:left;\"><label for=\"scrolling_accolades-char_limit\">" . __('Character limit', 'scrolling-accolades') . " </label><input class=\"widefat\" type=\"text\" id=\"scrolling_accolades-char_limit\" name=\"scrolling_accolades-char_limit\" value=\"" . htmlspecialchars($options['char_limit'], ENT_QUOTES) . "\" /></p>";
        echo "</div>";
        echo "<input type=\"hidden\" id=\"scrolling_accolades-submit\" name=\"scrolling_accolades-submit\" value=\"1\" />";
    }

    if (function_exists('wp_register_sidebar_widget')) {
        wp_register_sidebar_widget('scrolling_accolades', 'Random Quote', 'scrolling_accolades_widget');
        wp_register_widget_control('scrolling_accolades', 'Random Quote', 'scrolling_accolades_widget_control');
    } else {
        register_sidebar_widget(array('Random Quote', 'widgets'), 'scrolling_accolades_widget');
        register_widget_control('Random Quote', 'scrolling_accolades_widget_control', 250, 350);
    }
}

add_action('plugins_loaded', 'scrolling_accolades_widget_init');
?>
