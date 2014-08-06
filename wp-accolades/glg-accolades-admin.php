<?php

function glgaccolades_admin_menu() 
{
	global $glgaccolades_admin_userlevel;
	add_object_page('The Gerrigan Lyman Group: Accolades', 'Accolades', $glgaccolades_admin_userlevel, 'glg-accolades', 'glgaccolades_quotes_management', get_stylesheet_directory_uri().'/images/glg-icon.png');
}
add_action('admin_menu', 'glgaccolades_admin_menu');



function glgaccolades_addquote($quote, $author = "", $source = "", $tags = "", $public = 'yes')
{
	if(!$quote) return __('Nothing added to the database.', 'glg-accolades');
	global $wpdb;
	$table_name = $wpdb->prefix . "glgaccolades";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
		return __('Database table not found', 'glg-accolades');
	else //Add the quote data to the database
	{
		global $allowedposttags;
		$quote = wp_kses( stripslashes($quote), $allowedposttags );
		$author = wp_kses( stripslashes($author), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$source = wp_kses( stripslashes($source), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$tags = strip_tags( stripslashes($tags) );
		
		$quote = "'".$wpdb->escape($quote)."'";
		$author = $author?"'".$wpdb->escape($author)."'":"NULL";
		$source = $source?"'".$wpdb->escape($source)."'":"NULL";
		$tags = explode(',', $tags);
		foreach ($tags as $key => $tag)
			$tags[$key] = trim($tag);
		$tags = implode(',', $tags);
		$tags = $tags?"'".$wpdb->escape($tags)."'":"NULL";
		if(!$public) $public = "'no'";
		else $public = "'yes'";
		$insert = "INSERT INTO " . $table_name .
			"(quote, author, source, tags, public, time_added)" .
			"VALUES ({$quote}, {$author}, {$source}, {$tags}, {$public}, NOW())";
		$results = $wpdb->query( $insert );
		if(FALSE === $results)
			return __('There was an error in the MySQL query', 'glg-accolades');
		else
			return __('Quote added', 'glg-accolades');
   }
}

function glgaccolades_editquote($quote_id, $quote, $author = "", $source = "", $tags = "", $public = 'yes')
{
	if(!$quote) return __('Quote not updated.', 'glg-accolades');
	if(!$quote_id) return srgq_addquote($quote, $author, $source, $public);
	global $wpdb;
	$table_name = $wpdb->prefix . "glgaccolades";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
		return __('Database table not found', 'glg-accolades');
	else //Update database
	{
		global $allowedposttags;
		$quote = wp_kses( stripslashes($quote), $allowedposttags );
		$author = wp_kses( stripslashes($author), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$source = wp_kses( stripslashes($source), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$tags = strip_tags( stripslashes($tags) );

	  	$quote = "'".$wpdb->escape($quote)."'";
		$author = $author?"'".$wpdb->escape($author)."'":"NULL";
		$source = $source?"'".$wpdb->escape($source)."'":"NULL";
		$tags = explode(',', $tags);
		foreach ($tags as $key => $tag)
			$tags[$key] = trim($tag);
		$tags = implode(',', $tags);
		$tags = $tags?"'".$wpdb->escape($tags)."'":"NULL";
		if(!$public) $public = "'no'";
		else $public = "'yes'";
		$update = "UPDATE " . $table_name . "
			SET quote = {$quote},
				author = {$author},
				source = {$source}, 
				tags = {$tags},
				public = {$public}, 
				time_updated = NOW()
			WHERE quote_id = $quote_id";
		$results = $wpdb->query( $update );
		if(FALSE === $results)
			return __('There was an error in the MySQL query', 'glg-accolades');		
		else
			return __('Changes saved', 'glg-accolades');
   }
}


function glgaccolades_deletequote($quote_id)
{
	if($quote_id) {
		global $wpdb;
		$sql = "DELETE from " . $wpdb->prefix ."glgaccolades" .
			" WHERE quote_id = " . $quote_id;
		if(FALSE === $wpdb->query($sql))
			return __('There was an error in the MySQL query', 'glg-accolades');		
		else
			return __('Accolade deleted', 'glg-accolades');
	}
	else return __('The accolade cannot be deleted', 'glg-accolades');
}

function glgaccolades_getquotedata($quote_id)
{
	global $wpdb;
	$sql = "SELECT quote_id, quote, author, source, tags, public
		FROM " . $wpdb->prefix . "glgaccolades 
		WHERE quote_id = {$quote_id}";
	$quote_data = $wpdb->get_row($sql, ARRAY_A);	
	return $quote_data;
}

function glgaccolades_editform($quote_id = 0)
{
	$public_selected = " checked=\"checked\"";
	$submit_value = __('Add Quote', 'glg-accolades');
	$form_name = "addquote";
	$action_url = get_bloginfo('wpurl')."/wp-admin/admin.php?page=glg-accolades#addnew";
	$quote = $author = $source = $tags = $hidden_input = $back = "";

	if($quote_id) {
		$form_name = "editquote";
		$quote_data = glgaccolades_getquotedata($quote_id);
		foreach($quote_data as $key => $value)
			$quote_data[$key] = $quote_data[$key];
		extract($quote_data);
		$quote = htmlspecialchars($quote);
		$author = htmlspecialchars($author);
		$source = htmlspecialchars($source);
		$tags = implode(', ', explode(',', $tags));
		$hidden_input = "<input type=\"hidden\" name=\"quote_id\" value=\"{$quote_id}\" />";
		if($public == 'no') $public_selected = "";
		$submit_value = __('Save changes', 'glg-accolades');
		$back = "<input type=\"submit\" name=\"submit\" value=\"".__('Back', 'glg-accolades')."\" />&nbsp;";
		$action_url = get_bloginfo('wpurl')."/wp-admin/admin.php?page=glg-accolades";
	}

	$quote_label = __('The quote', 'glg-accolades');
	$author_label = __('Author', 'glg-accolades');
	$source_label = __('Date', 'glg-accolades');
	$tags_label = __('Post IDs', 'glg-accolades');
	$public_label = __('Public?', 'glg-accolades');
	$optional_text = __('optional', 'glg-accolades');
	$comma_separated_text = __('comma separated', 'glg-accolades');
	

	$display =<<< EDITFORM
<form name="{$form_name}" method="post" action="{$action_url}">
	{$hidden_input}
	<table class="form-table" cellpadding="5" cellspacing="2" width="100%">
		<tbody><tr class="form-field form-required">
			<th style="text-align:left;" scope="row" valign="top"><label for="glgaccolades_quote">{$quote_label}</label></th>
			<td><textarea id="glgaccolades_quote" name="quote" rows="5" cols="50" style="width: 97%;">{$quote}</textarea><p class="description">
			    <em>To link out to an external source, wrap the quote in an anchor tag, using a target attribute of "_blank", <br />
				e.g. &lt;a href=&quot;http://www.glg.com/&quot; target=&quot;_blank&quot;>The quote goes here.&lt;/a&gt;</p></td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="glgaccolades_author">{$author_label}</label></th>
			<td><input type="text" id="glgaccolades_author" name="author" size="40" value="{$author}" /><br />{$optional_text}</td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="glgaccolades_source">{$source_label}</label></th>
			<td><input type="text" id="glgaccolades_source" name="source" size="40" value="{$source}" /><br />{$optional_text}</td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="glgaccolades_tags">{$tags_label}</label></th>
			<td><input type="text" id="glgaccolades_tags" name="tags" size="40" value="{$tags}" /><br />{$optional_text}, {$comma_separated_text}</small></td>
		</tr>
		<tr>
			<th style="text-align:left;" scope="row" valign="top"><label for="glgaccolades_public">{$public_label}</label></th>
			<td><input type="checkbox" id="glgaccolades_public" name="public"{$public_selected} />
		</tr></tbody>
	</table>
	<p class="submit">{$back}<input name="submit" value="{$submit_value}" type="submit" class="button button-primary" /></p>
</form>
EDITFORM;
	return $display;
}

function glgaccolades_changevisibility($quote_ids, $public = 'yes')
{
	if(!$quote_ids)
		return __('Nothing done!', 'glg-accolades');
	global $wpdb;
	$sql = "UPDATE ".$wpdb->prefix."glgaccolades 
		SET public = '".$public."',
			time_updated = NOW()
		WHERE quote_id IN (".implode(', ', $quote_ids).")";
	$wpdb->query($sql);
	if($public == 'yes')
		return __("Selected quotes made public", 'glg-accolades');
	else
		return __("Selected quotes made private", 'glg-accolades');
}

function glgaccolades_bulkdelete($quote_ids)
{
	if(!$quote_ids)
		return __('Nothing done!', 'glg-accolades');
	global $wpdb;
	$sql = "DELETE FROM ".$wpdb->prefix."glgaccolades 
		WHERE quote_id IN (".implode(', ', $quote_ids).")";
	$wpdb->query($sql);
	return __('Quote(s) deleted', 'glg-accolades');
}



function glgaccolades_quotes_management()
{	

	global $glgaccolades_db_version;
	$options = get_option('glgaccolades');
	$display = $msg = $quotes_list = $alternate = "";
	
	if($options['db_version'] != $glgaccolades_db_version )
		glgaccolades_install();
		
	if(isset($_REQUEST['submit'])) {
		if($_REQUEST['submit'] == __('Add Quote', 'glg-accolades')) {
			extract($_REQUEST);
			$msg = glgaccolades_addquote($quote, $author, $source, $tags, $public);
		}
		else if($_REQUEST['submit'] == __('Save changes', 'glg-accolades')) {
			extract($_REQUEST);
			$msg = glgaccolades_editquote($quote_id, $quote, $author, $source, $tags, $public);
		}
	}
	else if(isset($_REQUEST['action'])) {
		if($_REQUEST['action'] == 'editquote') {
			$display .= "<div class=\"wrap\">\n<h2>The Gerrigan Lyman Group: Accolades &raquo; ".__('Edit accolade', 'glg-accolades')."</h2>";
			$display .=  glgaccolades_editform($_REQUEST['id']);
			$display .= "</div>";
			echo $display;
			return;
		}
		else if($_REQUEST['action'] == 'delquote') {
			$msg = glgaccolades_deletequote($_REQUEST['id']);
		}
	}
	else if(isset($_REQUEST['bulkactionsubmit']))  {
		if($_REQUEST['bulkaction'] == 'delete') 
			$msg = glgaccolades_bulkdelete($_REQUEST['bulkcheck']);
		if($_REQUEST['bulkaction'] == 'make_public') {
			$msg = glgaccolades_changevisibility($_REQUEST['bulkcheck'], 'yes');
		}
		if($_REQUEST['bulkaction'] == 'keep_private') {
			$msg = glgaccolades_changevisibility($_REQUEST['bulkcheck'], 'no');
		}
	}
	
	
	$display .= "<div class=\"wrap\">";
	
	if($msg)
		$display .= "<div id=\"message\" class=\"updated fade\"><p>{$msg}</p></div>";

	$display .= "<h2><img src=\"".get_stylesheet_directory_uri().'/images/glg-icon.png'."\" />&nbsp;The Gerrigan Lyman Group: Accolades <a href=\"#addnew\" class=\"add-new-h2\">".__('Add new accolade', 'glg-accolades')."</a></h2>";

	$num_quotes = glgaccolades_count();
	
	if(!$num_quotes) {
		$display .= "<p>".__('No accolades in the database', 'glg-accolades')."</p>";

		$display .= "</div>";
	
		$display .= "<div id=\"addnew\" class=\"wrap\">\n<h2>".__('Add new accolade', 'glg-accolades')."</h2>";
		$display .= glgaccolades_editform();
		$display .= "</div>";

		echo $display;
		return;
	}

	global $wpdb;

	$sql = "SELECT quote_id, quote, author, source, tags, public
		FROM " . $wpdb->prefix . "glgaccolades";
		
	$option_selected = array (
		'quote_id' => '',
		'quote' => '',
		'author' => '',
		'source' => '',
		'time_added' => '',
		'time_updated' => '',
		'public' => '',
		'ASC' => '',
		'DESC' => '',
	);
	if(isset($_REQUEST['orderby'])) {
		$sql .= " ORDER BY " . $_REQUEST['orderby'] . " " . $_REQUEST['order'];
		$option_selected[$_REQUEST['orderby']] = " selected=\"selected\"";
		$option_selected[$_REQUEST['order']] = " selected=\"selected\"";
	}
	else {
		$sql .= " ORDER BY quote_id ASC";
		$option_selected['quote_id'] = " selected=\"selected\"";
		$option_selected['ASC'] = " selected=\"selected\"";
	}
	
	if(isset($_REQUEST['paged']) && $_REQUEST['paged'] && is_numeric($_REQUEST['paged']))
		$paged = $_REQUEST['paged'];
	else
		$paged = 1;

	$limit_per_page = 20;
		
	
	
	$total_pages = ceil($num_quotes / $limit_per_page);
	
	
	if($paged > $total_pages) $paged = $total_pages;

	$admin_url = get_bloginfo('wpurl'). "/wp-admin/admin.php?page=glg-accolades";
	if(isset($_REQUEST['orderby']))
		$admin_url .= "&orderby=".$_REQUEST['orderby']."&order=".$_REQUEST['order'];
	
	$page_nav = glgaccolades_pagenav($total_pages, $paged, 2, 'paged', $admin_url);
	
	$start = ($paged - 1) * $limit_per_page;
		
	$sql .= " LIMIT {$start}, {$limit_per_page}"; 

	// Get all the quotes from the database
	$quotes = $wpdb->get_results($sql);
	
	foreach($quotes as $quote_data) {
		if($alternate) $alternate = "";
		else $alternate = " class=\"alternate\"";
		$quotes_list .= "<tr{$alternate}>";
		$quotes_list .= "<th scope=\"row\" class=\"check-column\"><input type=\"checkbox\" name=\"bulkcheck[]\" value=\"".$quote_data->quote_id."\" /></th>";
		$quotes_list .= "<td>" . $quote_data->quote_id . "</td>";
		$quotes_list .= "<td>";
		$quotes_list .= wptexturize(nl2br(make_clickable($quote_data->quote)));
    	$quotes_list .= "<div class=\"row-actions\"><span class=\"edit\"><a href=\"{$admin_url}&action=editquote&amp;id=".$quote_data->quote_id."\" class=\"edit\">".__('Edit', 'glg-accolades')."</a></span> | <span class=\"trash\"><a href=\"{$admin_url}&action=delquote&amp;id=".$quote_data->quote_id."\" onclick=\"return confirm( '".__('Are you sure you want to delete this quote?', 'glg-accolades')."');\" class=\"delete\">".__('Delete', 'glg-accolades')."</a></span></div>";
		$quotes_list .= "</td>";
		$quotes_list .= "<td>" . make_clickable($quote_data->author);
		if($quote_data->author && $quote_data->source)
			$quotes_list .= " / ";
		$quotes_list .= make_clickable($quote_data->source) ."</td>";
		$quotes_list .= "<td>" . implode(', ', explode(',', $quote_data->tags)) . "</td>";
		if($quote_data->public == 'no') $public = __('No', 'glg-accolades');
		else $public = __('Yes', 'glg-accolades');
		$quotes_list .= "<td>" . $public  ."</td>";
		$quotes_list .= "</tr>";
	}
	
	if($quotes_list) {
		$quotes_count = glgaccolades_count();

		$display .= "<form id=\"glgaccolades\" method=\"post\" action=\"".get_bloginfo('wpurl')."/wp-admin/admin.php?page=glg-accolades\">";
		$display .= "<div class=\"tablenav\">";
		$display .= "<div class=\"alignleft actions\">";
		$display .= "<select name=\"bulkaction\">";
		$display .= 	"<option value=\"0\">".__('Bulk Actions')."</option>";
		$display .= 	"<option value=\"delete\">".__('Delete', 'glg-accolades')."</option>";
		$display .= 	"<option value=\"make_public\">".__('Make public', 'glg-accolades')."</option>";
		$display .= 	"<option value=\"keep_private\">".__('Keep private', 'glg-accolades')."</option>";
		$display .= "</select>";	
		$display .= "<input type=\"submit\" name=\"bulkactionsubmit\" value=\"".__('Apply', 'glg-accolades')."\" class=\"button-secondary\" />";
		$display .= "&nbsp;&nbsp;&nbsp;";
		$display .= __('Sort by: ', 'glg-accolades');
		$display .= "<select name=\"orderby\">";
		$display .= "<option value=\"quote_id\"{$option_selected['quote_id']}>".__('Quote', 'glg-accolades')." ID</option>";
		$display .= "<option value=\"quote\"{$option_selected['quote']}>".__('Quote', 'glg-accolades')."</option>";
		$display .= "<option value=\"author\"{$option_selected['author']}>".__('Author', 'glg-accolades')."</option>";
		$display .= "<option value=\"source\"{$option_selected['source']}>".__('Source', 'glg-accolades')."</option>";
		$display .= "<option value=\"time_added\"{$option_selected['time_added']}>".__('Date added', 'glg-accolades')."</option>";
		$display .= "<option value=\"time_updated\"{$option_selected['time_updated']}>".__('Date updated', 'glg-accolades')."</option>";
		$display .= "<option value=\"public\"{$option_selected['public']}>".__('Visibility', 'glg-accolades')."</option>";
		$display .= "</select>";
		$display .= "<select name=\"order\"><option{$option_selected['ASC']}>ASC</option><option{$option_selected['DESC']}>DESC</option></select>";
		$display .= "<input type=\"submit\" name=\"orderbysubmit\" value=\"".__('Go', 'glg-accolades')."\" class=\"button-secondary\" />";
		$display .= "</div>";
		$display .= '<div class="tablenav-pages"><span class="displaying-num">'.sprintf(_n('%d quote', '%d quotes', $quotes_count, 'glg-accolades'), $quotes_count).'</span><span class="pagination-links">'. $page_nav. "</span></div>";
		$display .= "<div class=\"clear\"></div>";	
		$display .= "</div>";
		

		
		$display .= "<table class=\"widefat\">";
		$display .= "<thead><tr>
			<th class=\"check-column\"><input type=\"checkbox\" onclick=\"glgaccolades_checkAll(document.getElementById('glgaccolades'));\" /></th>
			<th>ID</th><th>".__('The quote', 'glg-accolades')."</th>
			<th>
				".__('Author', 'glg-accolades')." / ".__('Source', 'glg-accolades')."
			</th>
			<th>".__('Post IDs', 'glg-accolades')."</th>
			<th>".__('Public?', 'glg-accolades')."</th>
		</tr></thead>";
		$display .= "<tbody id=\"the-list\">{$quotes_list}</tbody>";
		$display .= "</table>";

		$display .= "<div class=\"tablenav\">";
		$display .= '<div class="tablenav-pages"><span class="displaying-num">'.sprintf(_n('%d quote', '%d quotes', $quotes_count, 'glg-accolades'), $quotes_count).'</span><span class="pagination-links">'. $page_nav. "</span></div>";
		$display .= "<div class=\"clear\"></div>";	
		$display .= "</div>";

		$display .= "</form>";
		$display .= "<br style=\"clear:both;\" />";

	}
	else
		$display .= "<p>".__('No accolades in the database', 'glg-accolades')."</p>";



	$display .= "</div>";
	
	$display .= "<div id=\"addnew\" class=\"wrap\">\n<h2>".__('Add new accolade', 'glg-accolades')."</h2>";
	$display .= glgaccolades_editform();
	$display .= "</div>";
	

	echo $display;

}


function glgaccolades_admin_footer()
{
	?>
<script type="text/javascript">
function glgaccolades_checkAll(form) {
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox" && !(form.elements[i].hasAttribute('onclick'))) {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
		}
	}
}
</script>

	<?php
}

add_action('admin_footer', 'glgaccolades_admin_footer');

?>
