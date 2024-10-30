<div class="likertm-survey" id="likertm-survey-div-<?php echo $survey->id?>">
<form method="post" id="likertm-survey-form-<?php echo $survey->id?>">
   <?php if(!empty($survey->ask_for_email) or !empty($survey->ask_for_name)):?>  
      <div class="likertm-contact-area" id="likertm-contact-wrap-<?php echo $survey->id?>">
         <?php if(!empty($survey->ask_for_name)):?>
            <p><label><?php _e('Your name:', 'likertm');?></label> <input type="text" name="likertm_name"></p>
         <?php endif;   
         if(!empty($survey->ask_for_email)):?>
         <p><label><?php _e('Your email address:', 'likertm');?></label> <input type="text" name="likertm_email"></p>
         <?php endif;?>
         <p><input type="button" value="<?php _e('Start Survey', 'likertm');?>" onclick="LikertSurvey.askForContact(<?php echo $survey->id?>);"></p>
      </div>
   <?php endif;?>
	<div class="likertm-survey-area" id="likertm-survey-wrap-<?php echo $survey->id?>" style="display:<?php echo (empty($survey->ask_for_email) and empty($survey->ask_for_name)) ? 'block' : 'none';?>">
		<?php foreach($questions as $question):?>	
			<div class="likertm-survey-question">
				<?php echo $_question->display_question($question);?>
			</div>
			
			<div class="likertm-survey-choices">
					<?php echo $_question->display_choices($question, $question->choices);?>
			</div>
		<?php endforeach;?>
	
		<div class="likertm-survey-action">
			<input type="button" id="likertm-survey-action-<?php echo $survey->id?>" value="<?php _e('Submit Survey', 'likertm')?>" onclick="LikertSurvey.submit(<?php echo $survey->id?>, '<?php echo admin_url('admin-ajax.php')?>');">
		</div>	
		<input type="hidden" name="survey_id" value="<?php echo $survey->id?>">
	</div>
</form>