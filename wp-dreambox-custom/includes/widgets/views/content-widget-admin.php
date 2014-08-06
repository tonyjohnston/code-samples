<p>Select a category to display:</p>
<?php wp_dropdown_categories(array( 
		'hide_empty' => 0,
		'selected'=>$instance['content-preview-cat'],
		'id'=>$this->get_field_id( 'content-preview-cat'),
		'name'=>$this->get_field_name( 'content-preview-cat'),
		'child_of'           => get_cat_ID("Featured Content"),
		)); ?>

<p>Max number of items to display:</p>
<p>
	<select id="<?php echo $this->get_field_id( 'content-preview-count' ); ?>" name="<?php echo $this->get_field_name( 'content-preview-count' ); ?>">
	<?php 
		for($x=1;$x<11;$x++){
			$options = '<option value="'.$x.'"';
			if($x == $instance['content-preview-count']){
				$options .= ' selected="selected"';
			}
			$options .= '>'.$x.'</option>';
			echo $options;
		}
	?>
	</select>
	<br />
</p>
