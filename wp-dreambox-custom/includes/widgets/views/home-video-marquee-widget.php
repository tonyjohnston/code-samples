<?php
$post_id = $instance['home-video-marquee-id'];
$content = array();
$labels = array();
$selected = ' selected';
$video_section = array('autoplay', 'looping', 'brand');
$video_formats = array('mp4','webm','ogg');
$count = 0;

if(function_exists('get_field')) {
	$tagline = get_field('marquee_tagline', $post_id);
	$main_placeholder_image = get_field('small_placeholder_image', $post_id);
	$large_image = get_field('image', $post_id);
	$image_url = get_field('image_url', $post_id);
	$autoplay_video = get_field('autoplay_video', $post_id);
	$looping_video = get_field('looping_video', $post_id);
} else {
	return;
}

if (function_exists('has_sub_fields')) {
	foreach($video_section as $section) {
		$videos[] = apply_filters('the_content',get_field($section.'_video', $post_id));
	}
	$items = array();
	while(has_sub_field('featured_items', $post_id)) {
		$items[] = array('title' => get_sub_field('feature_title')
						,'link' => get_sub_field('feature_link')
						,'type' => get_sub_field('feature_type'));
	}
}
?>

<div class="container">
	<img src="<?php echo $main_placeholder_image['url'] ?>" alt="<?php echo $main_placeholder_image['alt'] ?>" class="small-img" />

	<?php if(!$autoplay_video){ ?>
		<?php if(!$looping_video){ ?>
			<img src="<?php echo $large_image['url'] ?>" alt="<?php echo $large_image['alt'] ?>" class="large-img" />
		<?php } ?>
	<?php } ?>

	<?php #autoplay, looped, and play button video ?>
	<div class="video-container">
		<?php
		foreach($videos as $video){
			echo $video;
		}
		?>
	</div>

	<?php if($image_url){ ?><a href="<?php echo $image_url ?>"><?php } ?>
		<div class="video-options">
			<h1><?php echo $tagline; ?></h1>
			<?php if($video){ ?>
				<button class="play-pause">Play/Pause</button>
			<?php } ?>
		</div>
	<?php if($image_url){ ?></a><?php } ?>
</div>
<script src="http://fast.wistia.net/static/embed_shepherd-v1.js"></script>
<?php
global $dreambox_output_extras;
if(!is_object($dreambox_output_extras)){
	$dreambox_output_extras = new stdClass();
}

	$dreambox_output_extras->home_feature_boxes = '<div class="feature-boxes">';
	foreach($items as $item){
		$dreambox_output_extras->home_feature_boxes .= '<div>';
		$dreambox_output_extras->home_feature_boxes .= '<a href="'.$item['link'].'" class="'.strtolower($item['type']).'">'.$item['title'].'</a>';
		$dreambox_output_extras->home_feature_boxes .= '</div>';
	}
	$dreambox_output_extras->home_feature_boxes .= '</div>';
?>
