<p><textarea rows="3" cols="40" name="<?php echo empty($choice->id)?'answers[]':'answer'.$choice->id?>"><?php echo stripslashes(@$choice->answer)?></textarea> <?php _e('Points:', 'likertm')?> <input type="text" size="4" name="<?php echo empty($choice->id)?'points[]':'points'.$choice->id?>" value="<?php echo @$choice->points?>"> 
<?php if(!empty($choice->id)):?>
	<input type="checkbox" name="dels[]" value="<?php echo $choice->id?>"> <?php _e('Delete this choice', 'likertm')?>
<?php endif;?></p>