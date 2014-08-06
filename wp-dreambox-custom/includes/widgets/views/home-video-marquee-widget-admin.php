<?php
global $post;
$args = array(
    'orderby'          => 'post_title',
    'order'            => 'DESC',
    'post_type'        => 'home-video-marquee',
    'post_status'      => 'publish',
    'number_posts'     => -1,
    'suppress_filters' => true );
$posts = get_posts($args);
?>

<p>Select a marquee video layout to display.</p>
<select id="<?php echo $this->get_field_id( 'home-video-marquee-id' ); ?>" name="<?php echo $this->get_field_name( 'home-video-marquee-id' ); ?>">
<option disabled="disabled">-- Select --</option>
 <?php
 foreach( $posts as $post ) { 
    setup_postdata($post) ;
    $selected = '';
    if($instance['home-video-marquee-id'] == $post->ID){
        $selected = ' selected="selected"';
    }
    echo '<option value="'.$post->ID.'"'.$selected.'">'.get_the_title().'</option>';
 } 
 ?>
 </select>