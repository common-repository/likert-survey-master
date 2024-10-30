<div class="wrap">
	<h1><?php printf(__('Add/Edit Question in "%s"', 'likertm'), stripslashes($survey->title))?></h1>
	
	<p><a href="admin.php?page=likertm_surveys"><?php _e('Back to surveys', 'likertm')?></a> | <a href="admin.php?page=likertm_questions&survey_id=<?php echo $survey->id?>"><?php _e('Back to questions', 'likertm')?></a>		
		| <a href="admin.php?page=chained_survey&action=edit&id=<?php echo $survey->id?>"><?php _e('Edit This Survey', 'likertm')?></a>
	</p>
	
	<form method="post" onsubmit="return likertmValidate(this);">
		<p><label><?php _e('Question contents', 'likertm')?></label> <?php echo wp_editor(stripslashes(@$question->question), 'question')?></p>
		
		<p><label><?php _e('Question category', 'likertm');?></label> <select name="cat_id">
			<option value="0"><?php _e('Uncategorized', 'likertm');?></option>
			<?php foreach($qcats as $cat):
				$selected = '';
				if(!empty($question->cat_id) and $question->cat_id == $cat->id) $selected = ' selected';?>
				<option value="<?php echo $cat->id?>"<?php echo $selected?>><?php echo stripslashes($cat->name);?></option>
			<?php endforeach;?>
		</select></p>
		
		<p><input type="checkbox" name="is_required" value="1" <?php if(!empty($question->is_required)) echo 'checked'?>> <?php _e('Answering this question is required.', 'likertm');?></p>
		
		<h3><?php _e('Choices/Answers for this question', 'likertm')?></h3>
		
		<p> <input type="button" value="<?php _e('Add more rows', 'likertm')?>" onclick="likertmAddChoice();" class="button button-primary"></p>
		
		<div id="answerRows">
			<?php if(!empty($choices) and sizeof($choices)):
				foreach($choices as $choice):
					include(LIKERTM_PATH."/views/choice.html.php");
				endforeach;
			endif;
			unset($choice);
			include(LIKERTM_PATH."/views/choice.html.php");?>
		</div>
		
		<p><input type="submit" value="<?php _e('Save question and answers','likertm')?>" class="button button-primary"></p>
		<input type="hidden" name="ok" value="1">
		<input type="hidden" name="survey_id" value="<?php echo $survey->id?>">
		<?php wp_nonce_field('likertm_question');?>
	</form>
</div>

<script type="text/javascript" >
numChoices = 1;
function likertmAddChoice() {
	html = '<?php ob_start();
	include(LIKERTM_PATH."/views/choice.html.php");
	$content = ob_get_clean();	
	$content = str_replace("\n", '', $content);
	echo $content; ?>';
	
	// the correct checkbox value
	numChoices++;	
	jQuery('#answerRows').append(html);
}

function likertmValidate(frm) {
	if(frm.title.value == '') {
		alert("<?php _e('Please enter question title', 'likertm')?>");
		frm.title.focus();
		return false;
	}
	
	return true;
}
</script>