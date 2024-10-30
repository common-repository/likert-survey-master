<div class="wrap">
	<h1><?php _e('Manage Likert Surveys', 'likertm');?></h1>
	
	<p><a href="admin.php?page=likert_master"><?php _e('Create new survey', 'likertm');?></a></p>
	
	<?php if(count($surveys)):?>
		<table class="widefat">
			<tr><th><?php _e('Survey title', 'likertm')?></th><th><?php _e('Shortcode', 'likertm');?></th><th><?php _e('Manage questions', 'likertm');?></th>
			<th><?php _e('View answers', 'likertm');?></th><th><?php _e('Edit/Delete', 'likertm');?></th></tr>
			<?php foreach($surveys as $survey):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><?php if(!empty($survey->link)): echo '<a href="'.$survey->link.'" target="_blank">'; endif; 
					echo stripslashes($survey->title);
					if(!empty($survey->link)): echo '</a>'; endif;?></td>
					<td><input type="text" readonly="readonly" value='[likertm id=<?php echo $survey->id?>]' onclick="this.select();"></td>
					<td><a href="admin.php?page=likertm_questions&survey_id=<?php echo $survey->id?>"><?php _e('Manage', 'likertm')?></a></td>
					<td><a href="admin.php?page=likertm_results&survey_id=<?php echo $survey->id?>"><?php _e('View', 'likertm')?></a></td>
					<td><a href="admin.php?page=likertm_survey&id=<?php echo $survey->id?>&action=edit"><?php _e('Edit', 'likertm')?></a>
					|
					<a href="#" onclick="likertmDelSurvey(<?php echo $survey->id?>);return false;"><?php _e('Delete', 'likertm');?></a></td>
				</tr>
			<?php endforeach;?>
		</table>
	<?php else:?>
		<p><?php _e('There are no surveys yet.', 'likertm');?></p>
	<?php endif;?>
	
	<p><?php printf(__('Did you know? You can create a lot more powerful surveys with <a href="%1$s" target="_blank">WatuPRO</a> and its Reporting module. <a href="%2$s" target="_blank">Learn how here</a>. There is also a <a href="%3$s" target="_blank">free add-on</a> to create likert survey easier, similer to how this plugin works.', 'watupro'), 'https://calendarscripts.info/watupro/?s=lsm', 'https://blog.calendarscripts.info/how-to-conduct-surveys-with-watupro/', 'https://blog.calendarscripts.info/likert-scale-survey-maker-for-watupro/');?></p>
</div>

<script type="text/javascript" >
function likertmDelSurvey(id) {
	if(confirm("<?php _e('Are you sure?', 'likertm');?>")) {
		window.location = 'admin.php?page=likertm_surveys&del=1&id=' + id;
	}
}
</script>