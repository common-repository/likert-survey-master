<?php
class LikertQcats {
	static function manage() {
		global $wpdb;
		
		if(!empty($_POST['add']) and check_admin_referer('likertm_qcat')) {
			$wpdb->query($wpdb->prepare("INSERT INTO ".LIKERTM_QCATS." SET name=%s", sanitize_text_field($_POST['name'])));
			likertm_redirect("admin.php?page=likertm_qcats");
		}
		
		if(!empty($_POST['save']) and check_admin_referer('likertm_qcat')) {
			$wpdb->query($wpdb->prepare("UPDATE ".LIKERTM_QCATS." SET name=%s WHERE id=%d", sanitize_text_field($_POST['name']), intval($_POST['id'])));
			likertm_redirect("admin.php?page=likertm_qcats");
		}
		
		if(!empty($_GET['del']) and check_admin_referer('likertm_qcat')) {
			$wpdb->query($wpdb->prepare("DELETE FROM ".LIKERTM_QCATS." WHERE id=%d", intval($_GET['id'])));
			likertm_redirect("admin.php?page=likertm_qcats");
		}
		
		// select all question categories
		$qcats = $wpdb->get_results("SELECT * FROM ".LIKERTM_QCATS." ORDER BY name");
		
		include(LIKERTM_PATH."/views/qcats.html.php");		
	} // end manage()
}