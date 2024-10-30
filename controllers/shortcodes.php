<?php
class LikertMasterShortcodes {
	// outputs global chart of the results of the user
	static function barchart($atts) {
		global $wpdb;
		
		$chart_type = @$atts['type'];
				
		// get the answers by the taking ID (taking ID is POST global)
		$answers = $wpdb->get_results($wpdb->prepare("SELECT tA.answer as answer, tA.points as points, 
			tA.question_id as question_id, tQ.question as question, tC.name as category, tQ.cat_id as cat_id
			FROM ".LIKERTM_USER_ANSWERS." tA JOIN ".LIKERTM_QUESTIONS." tQ ON tQ.id = tA.question_id
			LEFT JOIN ".LIKERTM_QCATS." tC ON tC.ID = tQ.cat_id
			WHERE tA.taking_id=%d ORDER BY tA.id", intval($_POST['likertm_current_taking_id'])));
			
		if(!sizeof($answers)) return '';	
			
		$output = '';	
			
		switch($chart_type) {		
			
			case 'cats':
				// charts for each category. For each category we have to select the scale (choices) from the 1st question
				$cats = array();
				$cat_ids = array();
				$has_uncategorized = false;
				foreach($answers as $answer) {
					if($answer->cat_id and !in_array($answer->cat_id, $cat_ids)) $cats[] = array("ID"=>$answer->cat_id, "name" => $answer->category, "first_q_id"=>$answer->question_id);
					if(empty($answer->category) and !$has_uncategorized) {
						$uncategorized = array("ID"=>$answer->cat_id, 
							"name" => __('Uncategorized', 'likertm'), "first_q_id"=>$answer->question_id);
						$has_uncategorized = true;
					}
					
					$cat_ids[] = $answer->cat_id;
				}
				
				// add uncategorized at the end?
				if($has_uncategorized) $cats[] = $uncategorized;
				
				// now for each category create chart
				foreach($cats as $cat) {
					$output .= "<h3>".stripslashes($cat['name'])."<h3>";
					
					// select the choices for this category
					$chocies = $wpdb->get_results($wpdb->prepare("SELECT answer, points FROM ".LIKERTM_CHOICES."
					WHERE question_id=%d ORDER BY sort_order DESC, ID DESC", $cat['first_q_id']));

					// construct cat_answers
					$cat_answers = array();
					foreach($answers as $answer) {
						if($answer->cat_id == $cat['ID']) $cat_answers[] = $answer;
					}					
					
					$output .= self :: construct_chart($chocies, $cat_answers);	
				}
			break;
			
			case 'global':
			default:
				// this chart will get the textual & numeric values of the choices from the 1st question
				// it's users responsibility not to mix different scales
				$chocies = $wpdb->get_results($wpdb->prepare("SELECT answer, points FROM ".LIKERTM_CHOICES."
					WHERE question_id=%d ORDER BY sort_order DESC, ID DESC", $answers[0]->question_id));
				$output .= self :: construct_chart($chocies, $answers);	
			break;
		}	
		
		return $output;
	} // end chart
	
	// helper function to return single chart based on $choices and $answers
	// used from chart() method for global and cats cases
	static function construct_chart($chocies, $answers) {		
		$table = '<table class="likert-master-chart"><tr><th>'.__('Question', 'likertm').'</th>';
		foreach($chocies as $choice) {
			$table .= "<th>".stripslashes($choice->answer)."</th>";
		}
		$table .= "</tr>";
		
		// now add the answers
		foreach($answers as $answer) {
			$table .= "<tr><td>".stripslashes($answer->question)."</td>";
			foreach($chocies as $choice) {				
				$style = ($answer->points >= $choice->points) ? "background-color:#aaa;" : "";				
				$table .= '<td style="'.$style.'">&nbsp;</td>';
			}
			$table .= "</tr>";
		}
		
		$table .= "</table>";
		
		return $table;
	}
	
	// display survey
	static function survey($atts) {
		global $wpdb;
		$survey_id = @$atts['id'];		
		if(empty($survey_id) or !is_numeric($survey_id)) return __('No survey to load', 'likertm');
		ob_start();
		LikertMaster :: display($survey_id);
		$content = ob_get_clean();
		return $content;
	}
}