<div class="wrap">
	<h1><?php printf(__('Stats Per Question for Survey "%s"', 'likertm'), stripslashes($survey->title));?></h1>
	
	<p><a href="admin.php?page=likertm_results&survey_id=<?php echo $survey->id;?>"><?php _e('Back to the results', 'likertm');?></a></p>
	
	<?php foreach($questions as $cnt=>$question):
	$cnt++;?>
		<h3><?php echo $cnt.". ".stripslashes($question->question);?></h3>
		
		<table class="widefat">
			<tr class="alternate"><th><?php _e('Answer or metric', 'likertm')?></th><th><?php _e('Value', 'likertm')?></th></tr>
			<?php $class = '';			 
				foreach($question->choices as $choice):
				$class = ('alternate' == $class) ? '' : 'alternate';?><tr class="<?php echo $class?>">
					<td><?php echo stripslashes($choice->answer)?></td><td><strong><?php echo $choice->times_selected?></strong> <?php _e('times selected', 'likertm')?> / <strong><?php echo $choice->percentage?>%</strong> </td>			
				</tr>
			<?php endforeach;?>
		</table>
	<?php endforeach; // end foreach question ?>
</div>