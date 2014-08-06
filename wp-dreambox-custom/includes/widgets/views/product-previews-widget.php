<!-- This file is used to markup the public-facing widget. -->
<?php
$post_id = $instance['product-preview-id'];
$content = array();
$labels = array();
$selected = ' selected';
$count = 0;
if (function_exists('has_sub_fields')) {
	while (has_sub_fields('product_preview', $post_id)) {
		$count++;
		$tab = get_sub_field('tab_data');
		$tab_label = $tab[0]['tab_label'];
		$tab_class_id  = $tab['0']['tab_icon'];
		
		remove_filter('acf_the_content', 'wpautop');
		$content[] = '<div id="'.$tab_class_id.'-'.$count.'" class="tab">'."\n"
				. '<h2 class="title">'.$tab_label.'</h2>'."\n"
				. '<div class="content"><h3 class="accent-header">'.get_sub_field('content_header').'</h3>'
				. get_sub_field('tab_content') . '</div>'."\n"
				. '</div>'."\n";	
		add_filter('acf_the_content', 'wpautop');
	
		$labels[] = '<li class="'.$tab_class_id.$selected.'">'
					. '<a href="#'.$tab_class_id.'-'.$count.'">' . $tab_label . '</a>'
					. '</li>';
		$selected = '';
	}
}
?>
	<ul class="tabs">
	<?php
		foreach ($labels as $label) {
			echo $label;
		}
	?>
	</ul>
	<?php
	foreach ($content as $copy) {
		echo $copy;
	}
?>