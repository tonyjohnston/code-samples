<?php

function scrolling_accolades_shortcode_output_format($quotes)
{
    $display = "";

    foreach ($quotes as $quote_data) {
        $display .= "<div class=\"scrolling_accolades-scroller\" id=\"quote-" . $quote_data['quote_id'] . "\">";
        $display .= scrolling_accolades_output_format($quote_data);
        $display .= "</div>\n";
    }
    return apply_filters('scrolling_accolades_shortcode_output_format', $display);
}


function scrolling_accolades_shortcodes($atts = array())
{
    extract(shortcode_atts(array(
        'limit' => 0,
        'id' => 0,
        'author' => true,
        'source' => true,
        'tags' => '',
        'orderby' => 'quote_id',
        'order' => 'ASC',
        'paging' => false,
        'limit_per_page' => 10
    ), $atts));

    $condition = " WHERE public = 'yes'";

    if (isset($quote_id) && is_numeric($quote_id)) $id = $quote_id;

    if ($id && is_numeric($id)) {
        $condition .= " AND quote_id = " . $id;

        if ($quote = scrolling_accolades_get_quotes($condition))
            return scrolling_accolades_shortcode_output_format($quote);
        else
            return "";
    }

    if ($author)
        $condition .= " AND author = '" . $author . "'";
    if ($source)
        $condition .= " AND source = '" . $source . "'";
    if ($tags) {
        $tags = html_entity_decode($tags);
        if (!$tags)
            break;
        $taglist = explode(',', $tags);
        $tags_condition = "";
        foreach ($taglist as $tag) {
            $tag = trim($tag);
            if ($tags_condition) $tags_condition .= " OR ";
            $tags_condition .= "tags = '{$tag}' OR tags LIKE '{$tag},%' OR tags LIKE '%,{$tag},%' OR tags LIKE '%,{$tag}'";
        }
        if ($tags_condition) $condition .= " AND " . $tags_condition;
    }


    if ($orderby == 'id' || !$orderby) $orderby = 'quote_id';
    else if ($orderby == 'date_added') $orderby = 'time_added';
    else if ($orderby == 'random' || $orderby == 'rand') {
        $orderby = 'RAND(UNIX_TIMESTAMP(NOW()))';
        $order = '';
        $paging = false;
    };
    $order = strtoupper($order);
    if ($order && $order != 'DESC')
        $order = 'ASC';

    $condition .= " ORDER BY {$orderby} {$order}";

    if ($paging == true || $paging == 1) {

        $num_quotes = scrolling_accolades_count($condition);

        $total_pages = ceil($num_quotes / $limit_per_page);


        if (!isset($_GET['quotes_page']) || !$_GET['quotes_page'] || !is_numeric($_GET['quotes_page']))
            $page = 1;
        else
            $page = $_GET['quotes_page'];

        if ($page > $total_pages) $page = $total_pages;

        if ($page_nav = scrolling_accolades_pagenav($total_pages, $page, 0, 'quotes_page'))
            $page_nav = '<div class="scrolling_accolades_pagenav">' . $page_nav . '</div>';

        $start = ($page - 1) * $limit_per_page;

        $condition .= " LIMIT {$start}, {$limit_per_page}";

//		return $condition;

        if ($quotes = scrolling_accolades_get_quotes($condition))
            return $page_nav . scrolling_accolades_shortcode_output_format($quotes) . $page_nav;
        else
            return "";

    } else if ($limit && is_numeric($limit))
        $condition .= " LIMIT " . $limit;

//	return $condition;

    if ($quotes = scrolling_accolades_get_quotes($condition))
        return scrolling_accolades_shortcode_output_format($quotes);
    else
        return "";
}

add_shortcode('scrolling_accolades', 'scrolling_accolades_shortcodes');
add_shortcode('quotcoll', 'scrolling_accolades_shortcodes');
add_shortcode('quotecoll', 'scrolling_accolades_shortcodes'); // just in case, somebody misspells the shortcode


/* Backward compatibility for [quote] */


function scrolling_accolades_displayquote($matches)
{
    if (!isset($matches[1]) || (isset($matches[1]) && !$matches[1]) || $matches[0] == "[quote|random]")
        $atts = array('orderby' => 'random', 'limit' => 1);
    else
        $atts = array('id' => $matches[1]);

    return scrolling_accolades_shortcodes($atts);
}


function scrolling_accolades_displayquotes_author($matches)
{
    return scrolling_accolades_shortcodes(array('author' => $matches[1]));
}


function scrolling_accolades_displayquotes_source($matches)
{
    return scrolling_accolades_shortcodes(array('source' => $matches[1]));
}

function scrolling_accolades_displayquotes_tags($matches)
{
    return scrolling_accolades_shortcodes(array('tags' => $matches[1]));
}

function scrolling_accolades_inpost($text)
{
    $start = strpos($text, "[quote|id=");
    if ($start !== FALSE) {
        $text = preg_replace_callback("/\[quote\|id=(\d+)\]/i", "scrolling_accolades_displayquote", $text);
    }
    $start = strpos($text, "[quote|random]");
    if ($start !== FALSE) {
        $text = preg_replace_callback("/\[quote\|random\]/i", "scrolling_accolades_displayquote", $text);
    }
    $start = strpos($text, "[quote|all]");
    if ($start !== FALSE) {
        $text = preg_replace_callback("/\[quote\|all\]/i", "scrolling_accolades_shortcodes", $text);
    }
    $start = strpos($text, "[quote|author=");
    if ($start !== FALSE) {
        $text = preg_replace_callback("/\[quote\|author=(.{1,})?\]/i", "scrolling_accolades_displayquotes_author", $text);
    }
    $start = strpos($text, "[quote|source=");
    if ($start !== FALSE) {
        $text = preg_replace_callback("/\[quote\|source=(.{1,})?\]/i", "scrolling_accolades_displayquotes_source", $text);
    }
    $start = strpos($text, "[quote|tags=");
    if ($start !== FALSE) {
        $text = preg_replace_callback("/\[quote\|tags=(.{1,})?\]/i", "scrolling_accolades_displayquotes_tags", $text);
    }
    return $text;
}

add_filter('the_content', 'scrolling_accolades_inpost', 7);
add_filter('the_excerpt', 'scrolling_accolades_inpost', 7);

?>
