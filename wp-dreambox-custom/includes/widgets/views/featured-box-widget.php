<?php
$post_id = $instance['featured-box-id'];

if (function_exists('has_sub_fields')) {
	while (has_sub_fields('hub_marquee', $post_id)) {
		$marquee_title = htmlentities(get_sub_field('title'));
		$marquee_video = get_sub_field('wistia_embed_code', $post_id);	
		$marquee_description = htmlentities(get_sub_field('description', $post_id));
		$marquee_cta_text = htmlentities(get_sub_field('cta_text'));
		$marquee_cta_link = htmlentities(get_sub_field('cta_link'));
	}
}


?>

<div class="ad">
	<a href="#">The Common Core</a>
	<div class="content">
		<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.</p>
		<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/ad-thumbnail.png" alt="#" />
	</div>
</div>