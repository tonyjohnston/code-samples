<?php
 global $post;
 $args = array(
	'orderby'          => 'post_title',
	'order'            => 'ASC',
	'post_type'        => 'hub_marquee',
	'post_status'      => 'publish',
    'number_posts'     => -1,
	'suppress_filters' => true );
 $posts = get_posts($args);
 ?>
<p>Select a hub video marquee layout to display.</p>
<select id="<?php echo $this->get_field_id( 'hub-video-marquee-id' ); ?>" name="<?php echo $this->get_field_name( 'hub-video-marquee-id' ); ?>">
<option disabled="disabled">-- Select --</option>
 <?php
 foreach( $posts as $post ) { 
    setup_postdata($post) ;
    $selected = '';
    if($instance['hub-video-marquee-id'] == $post->ID){
        $selected = ' selected="selected"';
    }
    echo '<option value="'.$post->ID.'"'.$selected.'">'.get_the_title().'</option>';
 } 
 ?>
 </select>