<div class="wrap">
	<h1><?php printf(__('Manage Questions in %s', 'likertm'), $survey->title);?> </h1>
	
	<p><a href="admin.php?page=likertm_surveys"><?php _e('Back to surveys', 'likertm')?></a>		
		| <a href="admin.php?page=likertm_survey&action=edit&id=<?php echo $survey->id?>"><?php _e('Edit This Survey', 'likertm')?></a>
	</p>
	
	<p><a href="admin.php?page=likertm_questions&action=add&survey_id=<?php echo $survey->id?>"><?php _e('Click here to add new question', 'likertm')?></a></p>
	<?php if(count($questions)):?>
		<table class="widefat">
			<tr><th>#</th><th><?php _e('ID', 'likertm')?></th><th><?php _e('Question', 'likertm')?></th><th><?php _e('Edit / Delete', 'likertm')?></th></tr>
			<?php foreach($questions as $cnt=>$question):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><?php if($count > 1 and $cnt):?>
						<a href="admin.php?page=likertm_questions&survey_id=<?php echo $survey->id?>&move=<?php echo $question->id?>&dir=up"><img src="<?php echo LIKERTM_URL."/img/arrow-up.png"?>" alt="<?php _e('Move Up', 'likertm')?>" border="0"></a>
					<?php else:?>&nbsp;<?php endif;?>
					<?php if($count > $cnt+1):?>	
						<a href="admin.php?page=likertm_questions&survey_id=<?php echo $survey->id?>&move=<?php echo $question->id?>&dir=down"><img src="<?php echo LIKERTM_URL."/img/arrow-down.png"?>" alt="<?php _e('Move Down', 'likertm')?>" border="0"></a>
					<?php else:?>&nbsp;<?php endif;?></td>					
					<td><?php echo $question->id?></td><td><?php echo stripslashes($question->question)?></td>
					<td><a href="admin.php?page=likertm_questions&action=edit&id=<?php echo $question->id?>&survey_id=<?php echo $survey->id?>"><?php _e('Edit', 'likertm')?></a> | <a href="#" onclick="likertmConfirmDelete(<?php echo $question->id?>);return false;"><?php _e('Delete', 'likertm')?></a></td>
				</tr>
			<?php endforeach;?>	
		</table>		
	<?php endif;?>
</div>

<script type="text/javascript" >
function likertmConfirmDelete(qid) {
	if(confirm("<?php _e('Are you sure?', 'likertm')?>")) {
		window.location = 'admin.php?page=likertm_questions&survey_id=<?php echo $survey->id?>&del=1&id='+qid;
	}
}
</script>