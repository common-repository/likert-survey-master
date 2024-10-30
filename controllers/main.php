<?php
if(!defined('ABSPATH')) exit;

// the liker scale maker 
class LikertMaster {
	static function create() {
		global $wpdb, $user_ID;
		
		// default to 0
		$exam_id = 0;
		$ask_for_name = empty($_POST['ask_for_name']) ? 0 : 1;
      $ask_for_email = empty($_POST['ask_for_email']) ? 0 : 1;
		
		if(!empty($_POST['ok']) and check_admin_referer('likertm_survey')) {
			// create the quiz or just add more questions to it
			if(empty($_POST['quiz_id'])) {
				// prepare final screen
				$final_screen = __('Thank you for completing this survey. ', 'likertm');
	
				if(!empty($_POST['barchart'])) $final_screen .= '<br>[likertm-barchart type="'.sanitize_text_field($_POST['barchart']).'"]';
															
				// create new quiz
				$wpdb->query($wpdb->prepare("INSERT INTO ".LIKERTM_SURVEYS." SET
					title=%s, final_screen=%s, added_on = NOW(), ask_for_name=%d, ask_for_email=%d", 
					sanitize_text_field($_POST['title']), $final_screen, $ask_for_name, $ask_for_email ));
				
				$exam_id = $wpdb->insert_id;
				$_POST['quiz_id'] = $exam_id; 	
			}
			else $exam_id = intval($_POST['quiz_id']);
			
			// construct scale (answers) for the questions on this page
			$choices = array();
			
			// calculate high end of the scale
			$high_end = (count($_POST['choices']) - 1) * intval($_POST['step']) + intval($_POST['low_end']);			
			
			foreach($_POST['choices'] as $cnt => $choice) {				
				$points = ($_POST['scale_mode'] == 'decrements') ? ($high_end - $cnt * intval($_POST['step'])) : (intval($_POST['low_end']) + $cnt * intval($_POST['step']));
				$choices[] = array("answer"=>$choice, "points"=>$points);
			}
			
			// now add the questions and answers
			$is_required = empty($_POST['all_required']) ? 0 : 1;
			foreach($_POST['questions'] as $cnt => $question) {
				if(empty($question)) continue;
				$sort_order = $cnt+1;
				$wpdb->query($wpdb->prepare("INSERT INTO ".LIKERTM_QUESTIONS." SET
					survey_id=%d, question=%s, cat_id=%d, is_required=%d, sort_order=%d",
					$exam_id, $question, intval($_POST['cat_ids'][$cnt]), $is_required, $sort_order));
				$qid = $wpdb->insert_id;
				
				// now insert choices
				foreach($choices as $ct => $choice) {
					$wpdb->query($wpdb->prepare("INSERT INTO ".LIKERTM_CHOICES." SET
						question_id=%d, answer=%s, points=%f, sort_order=%d",
						$qid, $choice['answer'], $choice['points'], ($ct+1)));
				}	
			}
			
			if(!empty($_POST['auto_publish'])) self :: auto_publish($exam_id);
			
			// if done, redirect to the quiz setup page
			if(!empty($_POST['done'])) likertm_redirect("admin.php?page=likertm_survey&id=".$exam_id."&action=edit");
		}
		
		// select question categories
		$qcats = $wpdb->get_results("SELECT * FROM ".LIKERTM_QCATS." ORDER BY name");
		
		$scale = empty($_POST['scale']) ? 'agreement': $_POST['scale'];
		$is_published = false;
		
		if(empty($_GET['id'])) include(LIKERTM_PATH."/views/main.html.php"); 
		else include(LIKERTM_PATH."/views/add-questions.html.php");
	}
	
	// edit existing survey - just title and final screen
	static function edit() {
		global $wpdb;
		if(!is_numeric($_GET['id'])) wp_die();
		
		$ask_for_name = empty($_POST['ask_for_name']) ? 0 : 1;
      $ask_for_email = empty($_POST['ask_for_email']) ? 0 : 1;
		
		if(!empty($_POST['ok']) and check_admin_referer('likertm_survey')) {			
			$wpdb->query($wpdb->prepare("UPDATE ".LIKERTM_SURVEYS." SET 
				title=%s, final_screen=%s, ask_for_name=%d, ask_for_email=%d WHERE id=%d", 
				sanitize_text_field($_POST['title']), $_POST['final_screen'], 
				$ask_for_name, $ask_for_email, intval($_GET['id'])));
				
			if(!empty($_POST['auto_publish'])) self :: auto_publish($_GET['id']);	
		}
		
		$survey = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_SURVEYS." WHERE id=%d", intval($_GET['id'])));
		
		// is this survey currently published?
		$is_published = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[likertm id=".intval($_GET['id'])."]%' 
				AND post_status='publish' AND post_title!=''");	
		
		include(LIKERTM_PATH."/views/edit-survey.html.php");
	} // end edit
	
	// manage surveys
	static function manage() {
		global $wpdb;
		
		if(!empty($_GET['del'])) {
			$_GET['id'] = intval($_GET['id']);
			
			// delete survey
			$wpdb->query($wpdb->prepare("DELETE FROM ".LIKERTM_SURVEYS." WHERE id=%d", $_GET['id']));
						
			// delete choices
			$wpdb->query($wpdb->prepare("DELETE FROM ".LIKERTM_CHOICES." 
				WHERE question_id IN (SELECT id FROM ".LIKERTM_QUESTIONS." WHERE survey_id=%d)", $_GET['id']));
			
			// delete questions
			$wpdb->query($wpdb->prepare("DELETE FROM ".LIKERTM_QUESTIONS." WHERE survey_id=%d", $_GET['id']));
			
			// delete results
			$wpdb->query($wpdb->prepare("DELETE FROM " . LIKERTM_USER_ANSWERS. " WHERE taking_id IN (SELECT id FROM ".LIKERTM_TAKINGS." WHERE survey_id=%d)", $_GET['id']));
			$wpdb->query($wpdb->prepare("DELETE FROM " . LIKERTM_TAKINGS. " WHERE survey_id=%d", $_GET['id']));
			
			likertm_redirect("admin.php?page=likertm_surveys");
		}
		
		$surveys = $wpdb->get_results("SELECT * FROM ".LIKERTM_SURVEYS." ORDER BY id");
		
		// now select all posts that have likertm shortcode in them
   	$posts = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} 
   		WHERE post_content LIKE '%[likertm %]%'
   		AND (post_status='publish' OR post_status='private')
   		AND post_title!=''
   		ORDER BY post_date DESC");	
   		
   	// match posts to surveys
   	foreach($surveys as $cnt => $survey) {
   		foreach($posts as $post) {
   			if(stristr($post->post_content,"[likertm id=".$survey->id."]")) {
   				$surveys[$cnt]->link = get_permalink($post->ID);			
   				break;
   			}
   		}
   	}
		
		include(LIKERTM_PATH."/views/surveys.html.php");
	} // end manage
	
	// display survey
	static function display($survey_id) {
		global $wpdb, $user_ID;
	   $_question = new LikertmQuestion();
	   
	   // select the quiz
	   $survey = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_SURVEYS." WHERE id=%d", $survey_id));
	   if(empty($survey->id)) wp_die(__('Survey not found', 'chained'));
	   
	   // completion ID already created?
		if(empty($_SESSION['likertm_completion_id'])) {			
			// do we need this?
		}
	   
		 // select all questions
		 $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".LIKERTM_QUESTIONS." WHERE survey_id=%d
		 	ORDER BY sort_order, id", $survey->id));
		 
		 // select all answers
		 $choices = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".LIKERTM_CHOICES." 
		 	WHERE question_id IN (SELECT id from ".LIKERTM_QUESTIONS." WHERE survey_id=%d) ORDER BY id", $survey->id));
		 	
		 // match choices to questions
		 foreach($questions as $cnt => $question) {
		 	$question_choices = array();
		 	foreach($choices as $choice) {
		 		if($choice->question_id == $question->id) $question_choices[] = $choice;
			}
			
			$questions[$cnt]->choices = $question_choices;
		 }
		 
		 include(LIKERTM_PATH."/views/display-survey.html.php");
	}
	
	// submit the survey
	static function submit() {
		global $wpdb, $user_ID;
		
		// insert taking
		$wpdb->query($wpdb->prepare("INSERT INTO ".LIKERTM_TAKINGS." 
			SET datetime=%s, user_id=%d, ip=%s, survey_id=%d, name=%s, email=%s", 
			current_time('mysql'), $user_ID, $_SERVER['REMOTE_ADDR'], intval($_POST['survey_id']),
			sanitize_text_field($_POST['likertm_name'] ?? ''), sanitize_email($_POST['likertm_email'] ?? '')));
		$taking_id = $wpdb->insert_id;	
		
		// select all question IDs to insert answers
		$questions = $wpdb->get_results($wpdb->prepare("SELECT id, question FROM ".LIKERTM_QUESTIONS." 
			WHERE survey_id=%d ORDER BY sort_order, id", intval($_POST['survey_id'])));
			
		$answers_output = '';	
		
		// insert details
		foreach($questions as $question) {
			$answers_output .= stripslashes($question->question);			
			
			// get points
			$answer = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".LIKERTM_CHOICES. " WHERE id=%d",  intval(@$_POST['question-'.$question->id])));	
			
			$answers_output .= "<br>" . (empty($answer->answer) ? __('Not answered', 'likertm') : $answer->answer)."<hr>";
			
			$wpdb->query($wpdb->prepare("INSERT INTO " . LIKERTM_USER_ANSWERS . " SET
				question_id=%d, answer=%s, points=%f, taking_id=%d", 
				$question->id, @$_POST['question-'.$question->id], @$answer->points, $taking_id));
		}
		
		// get the survey output and display it
		$_POST['likertm_current_taking_id'] = $taking_id;
		$survey = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . LIKERTM_SURVEYS." WHERE id=%d", intval($_POST['survey_id'])));
		
		$output = wpautop(stripslashes($survey->final_screen));
		
		// replace vars
		$output = str_replace('{{num-questions}}', count($questions), $output);
		$output = str_replace('{{all-answers}}', $answers_output, $output);
		
		// apply the_content filter
		$output = apply_filters('likertm_content', $output);
		
		// output
		return $output;
	} // end submit()
	
	static function auto_publish($survey_id) {
		global $wpdb;
	
		$post = array('post_content' => '[likertm id='.$survey_id.']', 'post_name'=> $_POST['title'], 
			'post_title'=>$_POST['title'], 'post_status'=>'publish');
		wp_insert_post($post);
	}
}
