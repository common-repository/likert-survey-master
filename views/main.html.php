<div class="wrap">
	<h1><?php _e('Create Likert Scale Survey', 'likertm');?></h1>
	
	<p><?php _e('The user interface here is simplified and no rich text editor is used on questions. Once the survey is created you will be able to edit all the questions and the quiz settings individually and publish the quiz.', 'likertm');?></p>
	
	<form method="post" onsubmit="return validateLikertForm(this);">
		<div style='display:<?php echo empty($_POST['quiz_id']) ? 'block': 'none';?>'>
			<p><label><?php _e('Survey title:', 'likertm');?></label> <input type="text" name="title" size="60" value="<?php echo empty($_POST['title']) ? '' : esc_attr($_POST['title'])?>"></p>
			
			<p><input type="checkbox" name="all_required" value="1"> <?php _e('All questions in this survey are required.', 'likertm');?></p>
			
			<p><?php _e('Ask for contact data at the beginning of the survey:', 'likertm');?> 
			   <input type="checkbox" name="ask_for_name" value="1"> <?php _e('Respondent name', 'likertm');?>
			   &nbsp;
			   <input type="checkbox" name="ask_for_email" value="1"> <?php _e('Respondent email address', 'likertm');?></p>
			
			<p><label><?php _e('Show bar chart from the answers:', 'likertm')?></label> <select name="barchart">
				<option value=""><?php _e("Don't show", 'likertm');?></option>
				<option value="global"><?php _e("One global chart", 'likertm');?></option>
				<option value="cats"><?php _e("One chart for each question category", 'likertm');?></option>				
			</select> <?php _e('(This can be changed later.)', 'likertm')?></p>
		</div>	
		
		<?php if(!empty($_POST['quiz_id'])):?>
			<h2><?php _e('Adding More Questions to the Survey', 'likertm');?></h2>
		<?php endif;?>
		
		<h3><?php _e('Scale options', 'likertm');?></h3>
		
		<p><?php _e('The scale options are valid for all the questions you are adding on this page. You can add many sets of questions with the same options. You can also add different sets of questions with different options by pressing "Add more questions" button and changing the options on the next page.', 'likertm');?></p>
		
		<p><label><?php _e('Scale', 'likertm')?></label> <select name="scale" onchange="changeLikertScale(this.form, this.value);">
			<option value="agreement" <?php if(!empty($_POST['scale']) and $_POST['scale'] == 'agreement') echo 'selected'?>><?php _e('Agreement', 'likertm');?></option>
			<option value="frequency" <?php if(!empty($_POST['scale']) and $_POST['scale'] == 'frequency') echo 'selected'?>><?php _e('Frequency', 'likertm');?></option>
			<option value="importance" <?php if(!empty($_POST['scale']) and $_POST['scale'] == 'importance') echo 'selected'?>><?php _e('Importance', 'likertm');?></option>
			<option value="likelihood" <?php if(!empty($_POST['scale']) and $_POST['scale'] == 'likelihood') echo 'selected'?>><?php _e('Likelihood', 'likertm');?></option>
			<option value="custom" <?php if(!empty($_POST['scale']) and $_POST['scale'] == 'custom') echo 'selected'?>><?php _e('Custom', 'likertm');?></option>
		</select> <span id="customNumber" style='display:<?php echo (empty($_POST['scale']) or $_POST['scale']!= 'custom') ? 'none': 'inline'?>;'><input type="text" size="3" value="<?php echo empty($_POST['custom_num']) ? 5 : $_POST['custom_num']?>" name="custom_num" onkeyup="changeCustomNum(this.value);" maxlength="2"></span></p>
		
		<div id="likertScaleOptions">
			<?php switch($scale):
				case 'frequency': ?>
					<input type="text" name="choices[]" value="<?php _e('Always', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Frequently', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Occasionally', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Rarely', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Never', 'likertm');?>" size="30"> 
				<?php break;
				case 'importance':?>
					<input type="text" name="choices[]" value="<?php _e('Very Important', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Important', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Moderately Important', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Of Little Importance', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Unimportant', 'likertm');?>" size="30">
				<?php break;
				case 'likelihood':?>
					<input type="text" name="choices[]" value="<?php _e('Almost Always True', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Usually True', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Occasionally True', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Usually Not True', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Almost Never True', 'likertm');?>" size="30">
				<?php break;
				case 'custom':
					for($i=1;$i<= $_POST['custom_num']; $i++):?>
					<input type="text" name="choices[]" value="<?php echo $_POST['choices'][$i-1]?>" size="30"> <br>
					<?php endfor;
				break;
				case 'agreement':
				default:?>
					<input type="text" name="choices[]" value="<?php _e('Strongly Agree', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Agree', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Neutral', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Disagree', 'likertm');?>" size="30"> <br>
					<input type="text" name="choices[]" value="<?php _e('Strongly Disagree', 'likertm');?>" size="30"> 											 
			<?php endswitch;?>		
		</div>
		
		<p><?php _e('Start of the scale:', 'likertm');?> <input type="text" name="low_end" value="-2" size="2" maxlength="3"> 
		<?php printf(__('Scale %s with:', 'likertm'), '<select name="scale_mode"><option value="increments">'.__('increments', 'likertm').'</option><option value="decrements">'.__('decrements', 'likertm').'</option></select>');?> <input type="text" name="step" size="3" maxlength="3" value="1"><br>
		<?php _e('Usually likert scales start with -2 or 0 and increment/decrement with 1 so you get -2,-1,0,1,2 points or 1,2,3,4,5 points. But you can also use any other numbers and decimals.', 'likertm')?></p>
		
		
		
		<h3><?php _e('Add Your Questions', 'likertm')?></h3>
		
		<p><?php _e('Add one question per box', 'likertm');?></p>
		
		<?php include(LIKERTM_PATH."/views/questions.html.php");?>
		
			<p><input type="checkbox" name="auto_publish" value="1" checked="checked"> <?php _e('Automatically publish this survey in new post once I hit the "Save" button. (The new post will be auto-generated with the quiz title used for post title.)', 'likertm')?></p>
		
		<p ><input type="submit" name="add_more" value="<?php _e('Add more questions', 'likertm');?>" class="button button-primary">
		<input type="submit" name="done" value="<?php _e('Done. Create the survey', 'likertm');?>" class="button button-primary"></p>
		
		<input type="hidden" name="ok" value="1">
		<input type="hidden" name="quiz_id" value="<?php echo $exam_id?>">
		<?php wp_nonce_field('likertm_survey');?>
	</form>
</div>

<script type="text/javascript" >
function changeLikertScale(frm, val) {
	var html = '';
	jQuery('#customNumber').hide();
	
	switch(val) {
		case 'agreement':
			var vals = ["<?php _e('Strongly Agree', 'likertm');?>",
							"<?php _e('Agree', 'likertm');?>",
							"<?php _e('Neutral', 'likertm');?>",							 
							"<?php _e('Disagree', 'likertm');?>",
							"<?php _e('Strongly Disagree', 'likertm');?>",]
		break;	
		
		case 'frequency':
			var vals = ["<?php _e('Always', 'likertm');?>", 
							"<?php _e('Frequently', 'likertm');?>",
							"<?php _e('Occasionally', 'likertm');?>",
							"<?php _e('Rarely', 'likertm');?>",
							"<?php _e('Never', 'likertm');?>"]
		break;
		
		case 'importance':
			var vals = ["<?php _e('Very Important', 'likertm');?>", 
							"<?php _e('Important', 'watuprolik');?>",
							"<?php _e('Moderately Important', 'likertm');?>",
							"<?php _e('Of Little Importance', 'likertm');?>",
							"<?php _e('Unimportant', 'likertm');?>"]
		break;
		
		case 'likelihood':
			var vals = ["<?php _e('Almost Always True', 'likertm');?>", 
							"<?php _e('Usually True', 'likertm');?>",
							"<?php _e('Occasionally True', 'likertm');?>",
							"<?php _e('Usually Not True', 'likertm');?>",
							"<?php _e('Almost Never True', 'likertm');?>"]
		break;		
	}
	
	if(val == 'custom') {
		jQuery('#customNumber').show();	
		// cutom scale should output empty boxes
		for(i=0; i < frm.custom_num.value; i++) {
			html += '<input type="text" name="choices[]" value="" size="30"> <br>';
		}
	} else {
		for(i=0; i < vals.length; i++) {
			html += '<input type="text" name="choices[]" value="'+vals[i]+'" size="30"> <br>';
		}
	}
	
	jQuery('#likertScaleOptions').html(html);
}

function changeCustomNum(val) {
	var html = '';
		
	for(i=0; i < val; i++) {
			html += '<input type="text" name="choices[]" value="" size="30"> <br>';
	}
		
	jQuery('#likertScaleOptions').html(html);	
}

function validateLikertForm(frm) {
	if(frm.title.value == '') {
		alert("<?php _e('Please enter survey title.', 'likertm')?>");
		frm.title.focus();
		return false;
	}
	
	if(isNaN(frm.low_end.value) || isNaN(frm.step.value)) {
		alert("<?php _e('The scale low end and increment values must be numeric.', 'likertm');?>");
		if(isNaN(frm.low_end.value)) frm.low_end.focus();
		else frm.step.focus();
		return false;
	}
	
	return true;
}
</script>