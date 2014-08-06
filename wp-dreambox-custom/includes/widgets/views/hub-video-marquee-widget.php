<?php
$post_id = $instance['hub-video-marquee-id'];

if (function_exists('has_sub_fields')) {
	while (has_sub_fields('hub_marquee', $post_id)) {
		$marquee_title = htmlentities(get_sub_field('title'));

		$marquee_video = dreambox_get_wistia_video($post_id);	
		$marquee_description = get_sub_field('description', $post_id);
		$marquee_cta_text = htmlentities(get_sub_field('cta_text'));
		$marquee_cta_link = htmlentities(get_sub_field('cta_link'));
		$marquee_feature_box = get_sub_field('featured_items');
	}
}
?>
<!-- Hub Video Marquee widget Markup at /plugins/wp-dreambox-custom/includes/widgets/views/hub-featured-widget.php -->
<div class="content">
	<h1><?php echo $marquee_title; ?></h1>
	<p><?php echo $marquee_description; ?></p>
	<h2><a href="<?php echo $marquee_cta_link; ?>"><?php echo $marquee_cta_text; ?></a></h2>
</div>

<div class="video">
	<?php echo $marquee_video; ?>
	<!-- <button class="play-pause">Play/Pause</button> -->
</div>
<!-- end Hub Marquee widget -->

<?php
global $dreambox_output_extras;

if(!is_object($dreambox_output_extras)){
	$dreambox_output_extras = new stdClass();
}
foreach($marquee_feature_box as $item){
	$dreambox_output_extras->hub_feature_boxes .= '<div class="featured-box">';
	$dreambox_output_extras->hub_feature_boxes .= '<a href="'.$item['feature_link'].'"><img src="'.$item['feature_image'].'" alt="'.$item['feature_title'].'" /></a>';
	$dreambox_output_extras->hub_feature_boxes .= '<div><hgroup><h3>'.$item['feature_type'].'</h3>';
	$dreambox_output_extras->hub_feature_boxes .= '<h2>'.$item['feature_title'].'</h2></hgroup>';
	$dreambox_output_extras->hub_feature_boxes .= '<p>'.$item['feature_description'].'</p>';
	$dreambox_output_extras->hub_feature_boxes .= '<a href="'.$item['feature_link'].'" class="view">Learn more</a>';
	$dreambox_output_extras->hub_feature_boxes .= '</div></div>';
}
?>
