<?php
// view results on survey
class LikertmResults {
	static function view() {
		global $wpdb;
		
		// select survey
		$survey = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_SURVEYS." WHERE id=%d", intval($_GET['survey_id'])));
		
		// select questions
		$questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".LIKERTM_QUESTIONS." 
			WHERE survey_id=%d ORDER BY sort_order,id", $survey->id));
		
		// select takings limit 20
		$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
		$limit = 10;
		$takings = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tT.*, tU.display_name as display_name 
			FROM ".LIKERTM_TAKINGS." tT LEFT JOIN {$wpdb->users} tU ON tU.ID = tT.user_id 
			WHERE survey_id=%d ORDER BY tT.id DESC LIMIT $offset,$limit", $survey->id));
			
		$cnt_takings = $wpdb->get_var("SELECT FOUND_ROWS()");
		
		$tids = array(0);
		foreach($takings as $taking) $tids[] = $taking->id;
		$tid_sql = implode(",", $tids);	
		
		// select all answers on the takings listed on the page
		$answers = $wpdb->get_results("SELECT tA.*, tC.answer as answer_text 
			FROM ".LIKERTM_USER_ANSWERS." tA JOIN ".LIKERTM_CHOICES." tC ON tC.id = tA.answer
			WHERE taking_id IN ($tid_sql)");
		
		// match answers & questions to takings
		foreach($takings as $cnt => $taking) {
			$user_answers = array();
			foreach($answers as $answer) {
				if($answer->taking_id == $taking->id) $user_answers[] = $answer;
			}
			
			$takings[$cnt]->answers = $user_answers;
		} // end foreach;
		
		$dateformat = get_option('date_format');
		include(LIKERTM_PATH."/views/view-results.html.php");
	} // end view()
	
	// calculate stats per question
	static function per_question() {
		global $wpdb;
		
		// select survey
		$survey = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_SURVEYS." WHERE id=%d", intval($_GET['survey_id'])));
		
		// select questions
		$questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".LIKERTM_QUESTIONS." 
			WHERE survey_id=%d ORDER BY sort_order,id", $survey->id));
		$qids = array(0);
		foreach($questions as $question) $qids[] = $question->id;
		$qid_sql = implode(", ", $qids);	
			
		// select choices
		$choices = $wpdb->get_results("SELECT * FROM ".LIKERTM_CHOICES." WHERE question_id IN ($qid_sql)");	
		
		// select all answers on the takings listed on the page
		$answers = $wpdb->get_results($wpdb->prepare("SELECT tA.*, tC.answer as answer_text 
			FROM ".LIKERTM_USER_ANSWERS." tA JOIN ".LIKERTM_CHOICES." tC ON tC.id = tA.answer
			JOIN ".LIKERTM_TAKINGS." tT ON tA.taking_id=tT.ID
			WHERE tT.survey_id=%d", $survey->id));
			
		// now do the matches
		foreach($questions as $cnt => $question) {
			$question_choices = array();
			$total_answers = $num_correct = 0; // total answers/choices on this question
			$question_answers = $question_correct_answers = 0;
			
			// fill choices along with times and % selected
			foreach($choices as $ct => $choice) {
				if($choice->question_id != $question->id) continue;
				
				$choice->times_selected = $choice->percentage = 0;
				
				foreach($answers as $answer) {
					if($answer->question_id != $question->id) continue;
					if($answer->answer != $choice->id) continue;					
				   $choice->times_selected++; 
					
					$total_answers++;					
				}
				
				$question_choices[] = $choice;
			}
			
			// now calculate the overall stats for the whole question
			$num_unanswered = 0;
			foreach($answers as $answer) {				
				if($answer->question_id == $question->id) {
					if(empty($answer->answer)) $num_unanswered++;
					$question_answers++;
				}
			}
						
			// now we have all times_selected. Let's calculate % for each choice
			$choices_selected = 0;
			foreach($question_choices as $ct=>$choice) {
				// if total answers is < $question_answers, means we are in textarea question
				// so always choose the bigger
				if($total_answers < $question_answers) $total_answers = $question_answers;								
				
				if($total_answers) $percent = round(($choice->times_selected / $total_answers) * 100);
				else $percent = 0;
				
				$question_choices[$ct]->percentage = $percent;
				$choices_selected += $choice->times_selected;
			}
			
			// add unanswered
			if($num_unanswered) {				
	 			$un_perc = $total_answers ? round(($num_unanswered / $total_answers) * 100) : 0; 
				$question_choices[] = (object)array("answer" => __('Unanswered', 'likertm'), "times_selected"=>$num_unanswered, 
					"percentage"=>$un_perc);
			}
			
			$questions[$cnt]->choices = $question_choices;			
			$questions[$cnt]->total_answers = $question_answers;
		} // end questions loop	
		
		include(LIKERTM_PATH."/views/stats-per-question.html.php");
	} // end per_question()
}