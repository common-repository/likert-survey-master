<?php
class LikertmQuestion {
	function add($vars) {
		global $wpdb;
		
		// sort order
		$sort_order = $wpdb->get_var($wpdb->prepare("SELECT MAX(sort_order) FROM ".LIKERTM_QUESTIONS."
			WHERE survey_id=%d", intval($vars['survey_id'])));
		$sort_order++;	 
		
		$is_required = empty($vars['is_required']) ? 0 : 1;
		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".LIKERTM_QUESTIONS." SET
			survey_id=%d, question=%s, sort_order=%d, cat_id=%d, is_required=%d", 
			intval($vars['survey_id']), $vars['question'], $sort_order, intval($vars['cat_id']), $is_required));
			
		if($result === false) throw new Exception(__('DB Error', 'likertm'));
		return $wpdb->insert_id;	
	} // end add
	
	function save($vars, $id) {
		global $wpdb;
		
		$is_required = empty($vars['is_required']) ? 0 : 1;
		$result = $wpdb->query($wpdb->prepare("UPDATE ".LIKERTM_QUESTIONS." SET
			survey_id=%d, question=%s, cat_id=%d, is_required=%d WHERE id=%d", 
			intval($vars['survey_id']), $vars['question'], intval($vars['cat_id']), $is_required, $id));
			
			
		if($result === false) throw new Exception(__('DB Error', 'likertm'));
		return true;	
	}
	
	// saves the choices on a question
	function save_choices($vars, $id) {
		global $wpdb;
		
		// edit/delete existing choices
		$choices = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".LIKERTM_CHOICES." WHERE question_id=%d ORDER BY id ", $id));
		
		foreach($choices as $choice) {
			if(!empty($_POST['dels']) and in_array($choice->id, $_POST['dels'])) {
				$wpdb->query($wpdb->prepare("DELETE FROM ".LIKERTM_CHOICES." WHERE id=%d", $choice->id));
				continue;
			}
			
			// else update
			$wpdb->query($wpdb->prepare("UPDATE ".LIKERTM_CHOICES." SET
				answer=%s, points=%s WHERE id=%d", 
				$_POST['answer'.$choice->id], floatval($_POST['points'.$choice->id]), $choice->id));
		}	
		
		// add new choices
		$counter = 1;
		foreach($_POST['answers'] as $answer) {
			$counter++;
			if($answer === '') continue;
		
			// now insert the choice
			$wpdb->query( $wpdb->prepare("INSERT INTO ".LIKERTM_CHOICES." SET
				question_id=%d, answer=%s, points=%s", 
				$id, $answer, floatval($_POST['points'][($counter-2)])) );
		}
	} // end save_choices

	// displays the question contents
	function display_question($question) {
		// for now only add stripslashes and autop, we'll soon add a filter like in Watupro
		$content = stripslashes($question->question);
		$content = wpautop($content);
		
		if($question->is_required) {
			$content .= '<input type="hidden" class="likertm-required-'.$question->survey_id.'" value="'.$question->id.'">';
		}
		return $content;
	}

  // displays the possible choices on a question
  function display_choices($question, $choices) {  
		$output = "";
		foreach($choices as $choice) {
			$choice_text = stripslashes($choice->answer);
			
			$output .= "<div class='likertm-choice'><label class='likertm-label'><input class='likertm-frontend' type='radio' name='question-".$question->id."' value='".$choice->id."'> $choice_text</label></div>";
		}
				
		return $output;		
  } // end display_choices 
}