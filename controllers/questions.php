<?php
class LikertmQuestions {
	static function  manage() {
		global $wpdb;
		$_question = new LikertmQuestion();
		$action = empty($_GET['action']) ? 'list' : $_GET['action'];
		
		$_GET['survey_id'] = intval($_GET['survey_id']);
		
		// select survey
		$survey = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_SURVEYS." WHERE id=%d", $_GET['survey_id']));		
		
		// select question categories
		$qcats = $wpdb->get_results("SELECT * FROM ".LIKERTM_QCATS." ORDER BY name");
		
		switch($action) {
			case 'add':
				if(!empty($_POST['ok']) and check_admin_referer('likertm_question')) {
					try {
						$_POST['survey_id'] = $_GET['survey_id'];
						$qid = $_question->add($_POST);		
						$_question->save_choices($_POST, $qid);	
						likertm_redirect("admin.php?page=likertm_questions&survey_id=".$_GET['survey_id']);
					}
					catch(Exception $e) {
						$error = $e->getMessage();
					}
				}
				
				include(LIKERTM_PATH.'/views/question.html.php');
			break;
			
			case 'edit':
				if(!empty($_POST['ok']) and check_admin_referer('likertm_question')) {
					try {
						$_question->save($_POST, $_GET['id']);
						$_question->save_choices($_POST, $_GET['id']);
					}
					catch(Exception $e) {
						$error = $e->getMessage();
					}
				}			
			
				// select the quiz and question		
				$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_QUESTIONS." WHERE id=%d", intval($_GET['id'])));
						
				// select question choices
				$choices = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".LIKERTM_CHOICES." WHERE question_id=%d ORDER BY id ", $question->id));	
		
				include(LIKERTM_PATH."/views/question.html.php");
			break;			
			
			case 'list':
			default:
				if(!empty($_GET['del'])) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".LIKERTM_QUESTIONS." WHERE id=%d", intval($_GET['id'])));
					$wpdb->query($wpdb->prepare("DELETE FROM ".LIKERTM_CHOICES." WHERE question_id=%d", intval($_GET['id'])));
					
					likertm_redirect("admin.php?page=likertm_questions&survey_id=".$_GET['survey_id']);
				}		
				
				if(!empty($_GET['move'])) {
					// select question
					$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_QUESTIONS." WHERE id=%d", intval($_GET['move'])));
					
					if($_GET['dir'] == 'up') {
						$new_order = $question->sort_order - 1;
						if($new_order < 0) $new_order = 0;
						
						// shift others
						$wpdb->query($wpdb->prepare("UPDATE ".LIKERTM_QUESTIONS." SET sort_order=sort_order+1 
						  WHERE id!=%d AND sort_order=%d AND survey_id=%d", intval($_GET['move']), $new_order, $_GET['survey_id']));
					}
					else {
						$new_order = $question->sort_order+1;			
			
						// shift others
						$wpdb->query($wpdb->prepare("UPDATE ".LIKERTM_QUESTIONS." SET sort_order=sort_order-1 
			  				WHERE id!=%d AND sort_order=%d AND survey_id=%d", intval($_GET['move']), $new_order, $_GET['survey_id']));
					}
					
					// change this one
					$wpdb->query($wpdb->prepare("UPDATE ".LIKERTM_QUESTIONS." SET sort_order=%d WHERE id=%d", 
						$new_order, intval($_GET['move'])));
						
					// redirect 	
					chained_redirect('admin.php?page=likertm_questions&survey_id=' . $_GET['survey_id']);
				}	
			
				$questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".LIKERTM_QUESTIONS." WHERE survey_id=%d ORDER BY sort_order,id", $survey->id));
				$count = count($questions);  
				include(LIKERTM_PATH."/views/manage-questions.html.php");
			break;
		}
	} // end manage
}