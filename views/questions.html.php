<?php for($i=1; $i <= 8; $i++):?>
	<p><textarea name="questions[]" rows="2" cols="80"></textarea> 
	<?php _e('Category:', 'watuprolik');?> <select name="cat_ids[]">
		<option value=""><?php _e('Uncategorized', 'likertm');?>
		<?php foreach($qcats as $cat):?>
			<option value="<?php echo $cat->ID?>"><?php echo stripslashes($cat->name);?></option>
		<?php endforeach;?>
	</select></p>
<?php endfor;?>