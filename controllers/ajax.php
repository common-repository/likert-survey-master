<?php
// handle all ajax
function likertm_ajax() {
	$action = empty($_POST['likertm_action']) ? 'submit' : $_POST['likertm_action'];
	
	switch($action) {
		// submit the survey
		case 'submit':
		default:
			echo LikertMaster :: submit();
		break;
	}

	exit;
}