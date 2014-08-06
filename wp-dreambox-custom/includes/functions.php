<?php

function dreambox_custom_taxonomies() {
	$all_types = get_post_types(array('_builtin' => false),'names');
	$all_types['post'] = 'post';
	$all_types['page'] = 'page';
	
	unset($all_types['acf']);
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name' => _x('States', 'taxonomy general name'),
		'singular_name' => _x('State', 'taxonomy singular name'),
		'search_items' => __('Search States'),
		'all_items' => __('All States'),
		'parent_item' => __('Parent State'),
		'parent_item_colon' => __('Parent State:'),
		'edit_item' => __('Edit State'),
		'update_item' => __('Update State'),
		'add_new_item' => __('Add New State'),
		'new_item_name' => __('New State Name'),
		'menu_name' => __('State'),
	);

	register_taxonomy('dreambox_us_state', $all_types, array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'state'),
	));

	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name' => _x('Countries', 'taxonomy general name'),
		'singular_name' => _x('Country', 'taxonomy singular name'),
		'search_items' => __('Search Countries'),
		'all_items' => __('All Countries'),
		'parent_item' => __('Parent Country'),
		'parent_item_colon' => __('Parent Country:'),
		'edit_item' => __('Edit Country'),
		'update_item' => __('Update Country'),
		'add_new_item' => __('Add New Country'),
		'new_item_name' => __('New Country Name'),
		'menu_name' => __('Country'),
	);

	register_taxonomy('dreambox_country', $all_types, array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'country'),
	));

	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name' => _x('Implementation Models', 'taxonomy general name'),
		'singular_name' => _x('Model', 'taxonomy singular name'),
		'search_items' => __('Search Models'),
		'all_items' => __('All Models'),
		'parent_item' => __('Parent Model'),
		'parent_item_colon' => __('Parent Model:'),
		'edit_item' => __('Edit Model'),
		'update_item' => __('Update Model'),
		'add_new_item' => __('Add New Model'),
		'new_item_name' => __('New Model Name'),
		'menu_name' => __('Imp. Model'),
	);

	register_taxonomy('dreambox_implementation_model', $all_types, array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'imp-model'),
	));

	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name' => _x('Personas', 'taxonomy general name'),
		'singular_name' => _x('Persona', 'taxonomy singular name'),
		'search_items' => __('Search Personas'),
		'all_items' => __('All Personas'),
		'parent_item' => __('Parent Persona'),
		'parent_item_colon' => __('Parent Persona:'),
		'edit_item' => __('Edit Persona'),
		'update_item' => __('Update Persona'),
		'add_new_item' => __('Add New Persona'),
		'new_item_name' => __('New Persona Name'),
		'menu_name' => __('Persona'),
	);

	register_taxonomy('dreambox_persona', $all_types, array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'persona'),
	));

// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name' => _x('Channels', 'taxonomy general name'),
		'singular_name' => _x('Channel', 'taxonomy singular name'),
		'search_items' => __('Search Channels'),
		'all_items' => __('All Channels'),
		'parent_item' => __('Parent Channel'),
		'parent_item_colon' => __('Parent Channel:'),
		'edit_item' => __('Edit Channel'),
		'update_item' => __('Update Channel'),
		'add_new_item' => __('Add New Channel'),
		'new_item_name' => __('New Channel Name'),
		'menu_name' => __('Channel'),
		'show_in_menu' => __('dreambox_taxonomy')
	);

	register_taxonomy('dreambox_channel', $all_types, array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'channel'),
	));
}

add_action('init', 'dreambox_custom_taxonomies');

/**
 * Put custom taxonomies in their own menu
 */
add_action( 'admin_menu', 'dreambox_add_page' );
function dreambox_add_page() {
	$taxonomies = get_taxonomies(array('_builtin' => false), 'objects');
	foreach($taxonomies as $tax){
		add_submenu_page( 'dreambox_taxonomy', $tax->labels->name, $tax->labels->name, 'edit_others_posts', 'edit-tags.php?taxonomy='.$tax->name);
	}
}

// highlight the proper top level menu
function dreambox_tax_menu_correction($parent_file) {
	global $current_screen;
	$taxonomy = $current_screen->taxonomy;
	if (in_array($taxonomy, get_taxonomies(array('_builtin' => false)))){
		$parent_file = 'dreambox_taxonomy';
	}
	return $parent_file;
}
add_action('parent_file', 'dreambox_tax_menu_correction');

/**
 * Setting the default terms for the custom taxonomies
 *
 * @uses    get_terms
 * @uses    wp_insert_term
 * @uses   	dreambox_get_us_states
 * @uses   	term_exists
 *
 * @since   1.0
 * @author  Tony Johnston
 */
function dreambox_default_terms() {

	$taxonomies = get_taxonomies();
	if (is_array($taxonomies)) {
		foreach ($taxonomies as $taxonomy) {
			$current_values = get_terms($taxonomy, array('hide_empty' => false));
			if (empty($current_values)) {
				$defaults = dreambox_get_default_taxonomy_values($taxonomy);
				if (!empty($defaults)) {
					foreach ($defaults as $default) {
						if (!term_exists($default['name'], $taxonomy)) {
							wp_insert_term($default['name'], $taxonomy, array('slug' => $default['short']));
						}
					}
				}
			}
		}
	}
}

add_action('init', 'dreambox_default_terms');

/**
 * Returns an array of US states with name and proper short form
 *
 * @return 	array
 *
 * @since   1.0
 * @author 	GLG Tony Johnston
 */
function dreambox_get_default_taxonomy_values($tax) {

	switch ($tax) {
		case 'dreambox_us_state':
			$values = array(
				'0' => array('name' => 'Alaska', 'short' => 'AK'),
				'1' => array('name' => 'American Samoa', 'short' => 'AS'),
				'2' => array('name' => 'Arizona', 'short' => 'AZ'),
				'3' => array('name' => 'Arkansas', 'short' => 'AR'),
				'4' => array('name' => 'California', 'short' => 'CA'),
				'5' => array('name' => 'Colorado', 'short' => 'CO'),
				'6' => array('name' => 'Conneticut', 'short' => 'CT'),
				'7' => array('name' => 'Delaware', 'short' => 'DE'),
				'8' => array('name' => 'District of Columbia', 'short' => 'DC'),
				'9' => array('name' => 'Federated States of Micronesia', 'short' => 'FM'),
				'10' => array('name' => 'Florida', 'short' => 'FL'),
				'11' => array('name' => 'Georgia', 'short' => 'GA'),
				'12' => array('name' => 'Guam', 'short' => 'GU'),
				'13' => array('name' => 'Hawaii', 'short' => 'HI'),
				'14' => array('name' => 'Idaho', 'short' => 'ID'),
				'15' => array('name' => 'Illinois', 'short' => 'IL'),
				'16' => array('name' => 'Indiana', 'short' => 'IN'),
				'17' => array('name' => 'Iowa', 'short' => 'IA'),
				'18' => array('name' => 'Kansas', 'short' => 'KS'),
				'19' => array('name' => 'Kentucky', 'short' => 'KY'),
				'20' => array('name' => 'Louisiana', 'short' => 'LA'),
				'21' => array('name' => 'Maine', 'short' => 'ME'),
				'22' => array('name' => 'Marshall Islands', 'short' => 'MH'),
				'23' => array('name' => 'Maryland', 'short' => 'MD'),
				'24' => array('name' => 'Massachusetts', 'short' => 'MA'),
				'25' => array('name' => 'Michigan', 'short' => 'MI'),
				'26' => array('name' => 'Minnesota', 'short' => 'MN'),
				'27' => array('name' => 'Mississippi', 'short' => 'MS'),
				'28' => array('name' => 'Missouri', 'short' => 'MO'),
				'29' => array('name' => 'Montana', 'short' => 'MT'),
				'30' => array('name' => 'Nebraska', 'short' => 'NE'),
				'31' => array('name' => 'Nevada', 'short' => 'NV'),
				'32' => array('name' => 'New Hampshire', 'short' => 'NH'),
				'33' => array('name' => 'New Jersey', 'short' => 'NJ'),
				'34' => array('name' => 'New Mexico', 'short' => 'NM'),
				'35' => array('name' => 'New York', 'short' => 'NY'),
				'36' => array('name' => 'North Carolina', 'short' => 'NC'),
				'37' => array('name' => 'North Dakota', 'short' => 'ND'),
				'38' => array('name' => 'Northern Mariana Islands', 'short' => 'MP'),
				'39' => array('name' => 'Ohio', 'short' => 'OH'),
				'40' => array('name' => 'Oklahoma', 'short' => 'OK'),
				'41' => array('name' => 'Oregon', 'short' => 'OR'),
				'42' => array('name' => 'Palau', 'short' => 'PW'),
				'43' => array('name' => 'Pennsylvania', 'short' => 'PA'),
				'44' => array('name' => 'Puerto Rico', 'short' => 'PR'),
				'45' => array('name' => 'Rhode Island', 'short' => 'RI'),
				'46' => array('name' => 'South Carolina', 'short' => 'SC'),
				'47' => array('name' => 'South Dakota', 'short' => 'SD'),
				'48' => array('name' => 'Tennessee', 'short' => 'TN'),
				'49' => array('name' => 'Texas', 'short' => 'TX'),
				'50' => array('name' => 'Utah', 'short' => 'UT'),
				'51' => array('name' => 'Vermont', 'short' => 'VT'),
				'52' => array('name' => 'Virgin Islands', 'short' => 'VI'),
				'53' => array('name' => 'Virginia', 'short' => 'VA'),
				'54' => array('name' => 'Washington', 'short' => 'WA'),
				'55' => array('name' => 'West Virginia', 'short' => 'WV'),
				'56' => array('name' => 'Wisconsin', 'short' => 'WI'),
				'57' => array('name' => 'Wyoming', 'short' => 'WY'),
			);
			break;
		case 'dreambox_country':
			$values = array(
				'0' => array('name' => 'Canada', 'short' => 'CAN')
			);
			break;
		case 'dreambox_implementation_model':
			$values = array(
				'0' => array('short' => 'adaptive_learning', 'name' => 'Adaptive Learning'),
				'1' => array('short' => '21st_century_skills', 'name' => '21st Century Skills'),
				'2' => array('short' => 'blended_learning', 'name' => 'Blended Learning'),
				'3' => array('short' => 'canada_standards', 'name' => 'Canadian Standards'),
				'4' => array('short' => 'closing_the_achievement_gap', 'name' => 'Closing the Achievement Gap'),
				'5' => array('short' => 'common_core', 'name' => 'Common Core'),
				'6' => array('short' => 'competency_based_learning', 'name' => 'Competency Based Learning'),
				'7' => array('short' => 'conceptual_understanding', 'name' => 'Conceptual Understanding'),
				'8' => array('short' => 'ell', 'name' => 'English Language Learners'),
				'9' => array('short' => 'efficacy_and_results', 'name' => 'Efficacy & Results'),
				'10' => array('short' => 'enrichment', 'name' => 'Enrichment'),
				'11' => array('short' => 'guide', 'name' => 'Guide'),
				'12' => array('short' => 'intervention', 'name' => 'Intervention'),
				'13' => array('short' => 'math_learning', 'name' => 'Math Learning'),
				'14' => array('short' => 'meeting_avp', 'name' => 'Meeting AYP'),
				'15' => array('short' => 'pd_trainging', 'name' => 'PD & Training'),
				'16' => array('short' => 'personalized_learning', 'name' => 'Personalized Learning'),
				'17' => array('short' => 'sol', 'name' => 'SOL'),
				'18' => array('short' => 'student_driven_learning', 'name' => 'Student Driven Learning'),
				'19' => array('short' => 'student_progress', 'name' => 'Student Progress'),
				'20' => array('short' => 'teks', 'name' => 'TEKS')
			);
			break;
		case 'dreambox_persona':
			$values = array(
				'0' => array('short' => 'parent', 'name' => 'Parent'),
				'1' => array('short' => 'math', 'name' => 'Math Coordinator/Curriculum'),
				'2' => array('short' => 'next_gen', 'name' => 'Next Gen'),
				'3' => array('short' => 'principal', 'name' => 'Principal'),
				'4' => array('short' => 'superintendent', 'name' => 'Superintendent'),
				'5' => array('short' => 'teacher', 'name' => 'Teacher'),
				'6' => array('short' => 'interventionist', 'name' => 'Interventionist')
			);
			break;
		case 'dreambox_channel':
			$values = array(
				'0' => array('short' => 'district', 'name' => 'District'),
				'1' => array('short' => 'school', 'name' => 'School'),
				'2' => array('short' => 'classroom', 'name' => 'Classroom'),
				'3' => array('short' => 'home', 'name' => 'Home'),
				'4' => array('short' => 'other', 'name' => 'Other')
			);
			break;
		default:
			$values = false;
	}
	return $values;
}

function dreambox_list_registered_sidebars($echo = true) {
	global $post;

	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="dream_sidebar_nonce" id="dream_sidebar_nonce" value="' . wp_create_nonce('dreambox-sidebar-' . $post->ID) . '" />';

	// Get the location data if its already been entered
	$sidebar_location = get_post_meta($post->ID, '_dreambox_sidebars', true);
	$output = '<select name="_dreambox_sidebars">';

	foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) {
		$output .= '<option value="' . $sidebar['id'] . '"' . ($sidebar_location == $sidebar['id'] ? ' selected="selected"' : '') . '>';
		$output .= ucwords($sidebar['name']);
		$output .= '</option>';
	}
	$output .= '</select>';
	if ($echo) {
		echo $output;
	} else {
		return $output;
	}
}

function dreambox_list_functional_pages($echo = true) {
	global $post;
	$functional_pages = array('dreambox-search-right-sidebar-featured' => 'Search right sidebar', 'archives' => 'Archives');
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="dream_functional_pages_nonce" id="dream_functional_pages_nonce" value="' . wp_create_nonce('dreambox-functional-pages-' . $post->ID) . '" />';

	// Get the location data if its already been entered
	$page_settings = get_post_meta($post->ID, '_dreambox_functional_pages', true);
	$output = '<select name="_dreambox_functional_pages">';
	$output .= '<option value="none">None</option>';
	foreach ($functional_pages as $slug => $page) {
		$output .= '<option value="' . $slug . '"' . ($page_settings == $slug ? ' selected="selected"' : '') . '>';
		$output .= $page;
		$output .= '</option>';
	}
	$output .= '</select>';
	if ($echo) {
		echo $output;
	} else {
		return $output;
	}
}

function add_dreambox_metaboxes() {
	// add_meta_box('dreambox_sidebars_select', 'Show item on', 'dreambox_list_registered_sidebars', 'featured_items', 'side', 'default');
	add_meta_box('dreambox_list_functional_pages', 'Show item on', 'dreambox_list_functional_pages', 'featured_items', 'side', 'default');
}

add_action('add_meta_boxes', 'add_dreambox_metaboxes');

function dreambox_save_postdata($post_id) {
	if (array_key_exists('_dreambox_sidebars', $_POST)) {
		if (!isset($_POST['dream_sidebar_nonce']) || !wp_verify_nonce($_POST['dream_sidebar_nonce'], 'dreambox-sidebar-' . $post_id)) {
			return;
		}
		update_post_meta($post_id, '_dreambox_sidebars', $_POST['_dreambox_sidebars']
		);
	}
	if (array_key_exists('_dreambox_functional_pages', $_POST)) {
		if (!isset($_POST['dream_functional_pages_nonce']) || !wp_verify_nonce($_POST['dream_functional_pages_nonce'], 'dreambox-functional-pages-' . $post_id)) {
			return;
		}
		update_post_meta($post_id, '_dreambox_functional_pages', $_POST['_dreambox_functional_pages']
		);
	}
}
add_action('save_post', 'dreambox_save_postdata');

// Action attached to content-widget to order items according to the user's state
function dreambox_sort_query_by_state($sortby, $thequery) {
	if(!empty($_GET['location']) || !empty($_SESSION['region_name'])){
		$region = $_GET['location'] ? $_GET['location']:$_SESSION['region_name'];
		$sortby = 'find_in_set("'.mysql_real_escape_string($region).'",`dreambox_us_state`), `post_date` DESC';
	}
    return $sortby;
}
