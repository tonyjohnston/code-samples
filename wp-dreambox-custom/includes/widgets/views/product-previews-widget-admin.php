<?php
 global $post;
 $args = array(
	'orderby'          => 'post_title',
	'order'            => 'DESC',
	'post_type'        => 'product-preview',
	'post_status'      => 'publish',
    'number_posts'     => -1,
	'suppress_filters' => true );
 $posts = get_posts($args);
 ?>

<p>Select a product preview to display.</p>
<select id="<?php echo $this->get_field_id( 'product-preview-id' ); ?>" name="<?php echo $this->get_field_name( 'product-preview-id' ); ?>">
<option disabled="disabled">-- Select --</option>
 <?php
 foreach( $posts as $post ) { 
    setup_postdata($post) ;
    $selected = '';
    if($instance['product-preview-id'] == $post->ID){
        $selected = ' selected="selected"';
    }
    echo '<option value="'.$post->ID.'"'.$selected.'">'.get_the_title().'</option>';
 } 
 ?>
 </select>
