<?php
 global $post;
 global $wpdb;
 
remove_filter('excerpt_more', 'responsive_auto_excerpt_more');
remove_filter( 'get_the_excerpt', 'responsive_custom_excerpt_more' );

//add_filter('posts_orderby', 'dreambox_sort_query_by_state', 10, 2);
$region = $_GET['location'] ? $_GET['location']:$_SESSION['region_code'];
$cat_name = get_cat_name($instance['content-preview-cat']);
$cat_slug = get_category($instance['content-preview-cat'])->slug;

$regional_query = "
		SELECT $wpdb->posts.ID FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
		LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
		LEFT JOIN $wpdb->terms ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
	    WHERE $wpdb->term_taxonomy.taxonomy = 'dreambox_us_state'
		AND $wpdb->terms.slug = '".$region."'
		AND $wpdb->posts.post_status = 'publish'
	    GROUP BY `ID`
		ORDER BY $wpdb->posts.post_date DESC";

$regional_posts = $wpdb->get_results($regional_query, ARRAY_N);

if(!empty($regional_posts)) {
	foreach($regional_posts as $regional_post) {
		$regional[] = $regional_post[0]; 
	}
	$region = implode(",", $regional);
}

$querystr = "
		SELECT * FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
	    WHERE $wpdb->term_taxonomy.taxonomy = 'category'
		AND $wpdb->terms.slug = '".$cat_slug."'
		AND $wpdb->posts.post_status = 'publish'
	    GROUP BY `ID`
		ORDER BY FIND_IN_SET($wpdb->posts.ID, '".$region."') DESC, `post_date` DESC
		LIMIT ".$instance['content-preview-count'];

if($cat_slug == 'featured-product') {
	
	$post_args = array(
		'post_type'			=> 'featured_items',
		'orderby'			=> 'post_date',
		'order'				=> 'DESC',
		'post_status'		=> 'publish',
		'numberposts'		=> $instance['content-preview-count']
	);
	
	$featured_items = get_field('featured_widget_items', $post->ID);
	
	if($featured_items){
		foreach($featured_items as $item){
			$item_ids[] = $item->ID;
		}
		$post_args['post__in'] = $item_ids;
	}else{
		$post_args['meta_query'] = array(
			array(
				'key' => '_dreambox_functional_pages',
				'value' => $args['id'],
			),
		);
	}
	$posts = get_posts($post_args);
}else{
	$posts = $wpdb->get_results($querystr, OBJECT);
}

$icon_classes = array('case-study','video','post','images','implementation-model','efficacy-and-results','guide','pd-training');

echo '<div class="'.$cat_slug.'">';

if(!in_array($cat_slug, array('contact-form','featured-product'))){
	echo '<h2>'.$cat_name.'</h2>';
}

if(is_array($posts)) {
	$widget_vall = "/".$cat_slug;
	foreach($posts as $post) {
		setup_postdata($post) ;
		$post_type = get_post_type_object( get_post_type($post) );
		$interest_area = array();
		setup_postdata($post);
		$implementation_model = wp_get_post_terms( $post->ID, 'dreambox_implementation_model' );
		if(!empty($implementation_model)) {
			$class_icon = $implementation_model[0];
			$slug_icon = str_replace('_','-',$class_icon->slug);
			if(!in_array($class_icon, $icon_classes)){
				$class_icon = $post_type;
			}
		}else{
			$class_icon = $post->post_type;
		}
		if(function_exists('get_field')){
			$field = get_field_object('content_interest_area');
			$value = get_field('content_interest_area', $post->ID);
			$interest_area = $field['choices'][$value[0]];
			$presenter = get_field('presenter', $post->ID);
			
			// Get filter fields
			while(has_sub_field('common_location',$post->ID)) {
				$region = get_sub_field('common_location_state', $post->ID);
			}
			while(has_sub_field('common_more_details',$post->ID)) {
				$implementation_model = get_sub_field('common_more_implementation_model', $post->ID);
				$persona = get_sub_field('common_more_persona', $post->ID);
				$channel = get_sub_field('common_more_channel', $post->ID);
			}
		}
		switch ($cat_slug) {
			case "testimonials":
				while(has_sub_field('common_location',$post->ID)) {
					$testimonial_bio = get_sub_field('common_location_people', $post->ID);
					$testimonial_bio = $testimonial_bio[0];
					$region = get_sub_field('common_location_state', $post->ID);
				}
				while(has_sub_field('people_name', $testimonial_bio->ID)){
					$testimonial_name = get_sub_field('title').' '.
							get_sub_field('first_name').' '.
							get_sub_field('middle_name').' '.
							get_sub_field('last_name');
				}
				while(has_sub_field('bio', $testimonial_bio->ID)) {
					while(has_sub_field('contact_information')) {
						$testimonial_image = get_sub_field('head_shot');
						$testimonial_company = get_sub_field('company');
						$testimoneial_job_title = get_sub_field('job_title');
					}
				}
				$output .= '<div class="testimonial"><blockquote><p>'.get_the_content().'</p><cite>';
				if($testimonial_image){
					$output .= '<img src="'.$testimonial_image['sizes']['square-avatar'].'" alt="'.$testimonial_name.'" />';
				}
				$output .= $testimonial_name;
				if($testimonial_company){
					$output .= ', <span itemprop="school">'.$testimonial_company.'</span> ';
				}
				if($testimonial_job_title) {
						$output .= '<span itemprop="title">'.$testimonial_job_title.'</span>';
				}
				$output .= '</cite></blockquote></div>';
				$widget_vall = get_post_type_archive_link($post_type->name);
				break;
			case 'blogs':
				$output .= '<div class="blog-snippet">';
				$output .=  str_replace(" height='60' width='60'", '', get_avatar( get_the_author_meta('ID'),60)); 
				$output .= '<div class="excerpt">';
				$output .= '<footer>'.$cat_name.' / <time itemprop="datePublished" datetime="YYYY-MM-DD">'.$post->post_date.'</time></footer>';
				$output .= '<h3><a href="'.get_permalink( get_the_ID() ).'">'.$post->post_title.'</a></h3>';
				$output .= '<p>'.get_the_excerpt().'</p>';
				$output .= '</div></div>';
				$widget_vall = get_permalink( get_page_by_title( 'Blog' ) );
				break;
			case 'contact-form':
			case 'featured-items':
			case 'featured-product':
				$output .= '<div class="ad">';
				$output .= '<a href="'.get_field('target_url').'">'.get_the_title().'</a>';
				$output .= '<div class="content">';
				$output .= '<p>'.get_the_excerpt().'</p>';
				$output .= '<a href="'.get_field('target_url').'"><img src="'.wp_get_attachment_url(get_post_thumbnail_id()).'" alt="'.get_the_title().'" /></a>';
				$output .= '</div></div>';
				break;
			default:
				$output .= '<div class="'.$cat_slug.'-snippet">';
				$output .= '<p class="product-type '.$slug_icon.'">'.$interest_area.' | '.$post_type->label.'</p>';
				$output .= '<h3><a href="'.get_permalink( get_the_ID() ).'">'.$post->post_title.'</a></h3>';	
				$output .= '<p>'.get_the_excerpt().'</p>'.'</div>';
						
		}
	}
	
	if(!in_array($cat_slug, array('contact-form','featured-items','featured-product'))){
		
		$output .= '<a href="'.$widget_vall.'" class="view">View all</a>';
	}
	echo $output;
}

?>
</div>

