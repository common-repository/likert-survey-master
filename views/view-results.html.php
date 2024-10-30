<div class="wrap">
	<h1><?php printf(__('View results on survey "%s"', 'likertm'), stripslashes($survey->title));?></h1>
	
	<p><a href="admin.php?page=likertm_surveys"><?php _e('Back to surveys', 'likertm');?></a>
	&nbsp;|&nbsp;
	<a href="admin.php?page=likertm_per_question&survey_id=<?php echo $survey->id;?>"><?php _e('Stats per quesiton', 'likertm');?></a></p>
	
	<?php if(count($takings)):?>
		<table class="widefat">
			<tr><th><?php _e('Username', 'likertm');?></th><th><?php _e('IP Address', 'likertm');?></th><th><?php _e('Date/Time', 'likertm');?></th>
			<th><?php _e('Answers', 'likertm');?></th></tr>
			<?php foreach($takings as $taking):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class;?>"><td><?php if($survey->ask_for_name and $taking->name) echo $taking->name;
				else echo $taking->user_id ? $taking->display_name : __('n/a', 'likertm');
				if($survey->ask_for_email and $taking->email) echo '<br>' . $taking->email;?></td>
				<td><?php echo $taking->ip?></td>
				<td><?php echo date($dateformat, strtotime($taking->datetime));?></td>
				<td>
					<table class="widefat">
						<tr><th><?php _e('Question', 'likertm');?></th><th><?php _e('Answer', 'likertm');?></th></tr>
						<?php foreach($questions as $question):
							$cls = ('alternate' == @$cls) ? '' : 'alternate';?>
							<tr class="<?php echo $cls;?>"><td><?php echo stripslashes($question->question);?></td><td>
								<?php foreach($taking->answers as $answer):
									if($answer->question_id == $question->id) echo stripslashes($answer->answer_text);?>
								<?php endforeach;?>
							</td></tr>
						<?php endforeach;?>
					</table>
				</td></tr>
			<?php endforeach;?>			
		</table>
		
		<p align="center">	
			<?php if($offset > 0):?>
				<a href="admin.php?page=likertm_results&survey_id=<?php echo $survey->id?>&offset=<?php echo $offset - $limit?>"><?php _e('Previous page', 'likertm');?></a>
			<?php endif;
				if($cnt_takings > ($offset + $limit)):?>
				&nbsp; <a href="admin.php?page=likertm_results&survey_id=<?php echo $survey->id?>&offset=<?php echo $offset + $limit?>"><?php _e('Next page', 'likertm')?></a>
			<?php endif;?>		
		</p>
	<?php else:?>
		<p><?php _e('No one has taken this survey yet.', 'likertm');?></p>
	<?php endif;?>
</div>