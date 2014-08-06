<table id="table_entries" class="w100">
    <thead><tr style="background-color: black; color: white;"><th></th><th>Tile title</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody id="tbody_entries"> 
<?php
    $row_count = 0;
    $background_color = '';
    $the_query = new WP_Query($args);
    if ($the_query->have_posts()) {
      while ($the_query->have_posts()) {
        if ($row_count % 2 == 0) {
          $background_color = 'background:#fff';
        } else {
          $background_color = 'background:#eee';
        }
        $the_query->the_post(); 

		if(function_exists('get_article_device_type')){
			$device = get_article_device_type(get_the_ID());
		}
		?>
      <tr style="<?php echo $background_color; ?>" id="entry_<?php the_ID(); ?>">
			  <td><?php the_post_thumbnail('tiny_thumbnail') ?></td>
	  <td><a href="<?php the_permalink() ?>" target="_blank">
            <?php $tileTitle = get_post_custom_values('tile_headline'); echo ($tileTitle[0] ? $tileTitle[0] : the_title()); ?></a>
			<?php echo ($device ? " - ".$device : ""); ?>
          </td>
		  <td><?php echo get_post_status(); ?></td>
          <td><?php the_time('j M, Y'); ?></td>
          <td id="action_entry_<?php the_ID(); ?>" style="cursor:pointer">Add</td>
        </tr> <?php
        $row_count++;
      }
      wp_reset_query();
    }?>
  </tbody>
</table>
