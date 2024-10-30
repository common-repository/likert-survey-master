<?php
/*
Plugin Name: Likert Survey Master
Plugin URI: http://calendarscripts.info/likert-master.html
Description: Plugin for performing Likert Surveys
Author: Kiboko Labs
Version: 0.8.0.1
Author URI: http://calendarscripts.info/
License: GPLv2 or later
Text Domain: likertm
*/

define( 'LIKERTM_PATH', dirname( __FILE__ ) );
define( 'LIKERTM_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'LIKERTM_URL', plugin_dir_url( __FILE__ ));

include(LIKERTM_PATH.'/models/question.php');
include(LIKERTM_PATH.'/controllers/shortcodes.php');
include(LIKERTM_PATH.'/controllers/actions.php');
include(LIKERTM_PATH.'/controllers/main.php');
include(LIKERTM_PATH.'/controllers/questions.php');
include(LIKERTM_PATH.'/controllers/qcats.php');
include(LIKERTM_PATH.'/controllers/ajax.php');
include(LIKERTM_PATH.'/controllers/results.php');

register_activation_hook( __FILE__, 'likertmaster_activate' );
add_action('init', 'likertmaster_init');

function likertmaster_activate($update = false) {		
	global $user_ID, $wpdb;
	if(!$update) likertmaster_init();
		
	// create database tables or add DB fields
	 // quizzes
     if($wpdb->get_var("SHOW TABLES LIKE '".LIKERTM_SURVEYS."'") != LIKERTM_SURVEYS) {  
         $sql = "CREATE TABLE `".LIKERTM_SURVEYS."` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`title` VARCHAR(255) NOT NULL DEFAULT '',
					`final_screen` TEXT,
					`added_on` DATE,
					PRIMARY KEY  (id)
				) CHARACTER SET utf8;";
         $wpdb->query($sql);         
     }  
     
      // questions
     if($wpdb->get_var("SHOW TABLES LIKE '".LIKERTM_QUESTIONS."'") != LIKERTM_QUESTIONS) {  
         $sql = "CREATE TABLE `".LIKERTM_QUESTIONS."` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`survey_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`question` TEXT,
					`cat_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`is_required` TINYINT UNSIGNED NOT NULL DEFAULT 0,
					`sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)
				) CHARACTER SET utf8;";
         $wpdb->query($sql);         
     }  
     
      // choices
     if($wpdb->get_var("SHOW TABLES LIKE '".LIKERTM_CHOICES."'") != LIKERTM_CHOICES) {  
         $sql = "CREATE TABLE `".LIKERTM_CHOICES."` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`question_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`answer` TEXT,
					`points` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
					`sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)
				) CHARACTER SET utf8;";
         $wpdb->query($sql);         
     }  
     
     // question categories
     if($wpdb->get_var("SHOW TABLES LIKE '".LIKERTM_QCATS."'") != LIKERTM_QCATS) {  
         $sql = "CREATE TABLE `".LIKERTM_QCATS."` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`name` VARCHAR(255) NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)
				) CHARACTER SET utf8;";
         $wpdb->query($sql);         
     }  
     
     // taken surveys
     if($wpdb->get_var("SHOW TABLES LIKE '".LIKERTM_TAKINGS."'") != LIKERTM_TAKINGS) {  
         $sql = "CREATE TABLE `".LIKERTM_TAKINGS."` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`datetime` DATETIME,
					`user_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`ip` VARCHAR(20) NOT NULL DEFAULT '',
					`survey_id` INT UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)
				) CHARACTER SET utf8;";
         $wpdb->query($sql);         
     }  
     
     // detailed answers
     if($wpdb->get_var("SHOW TABLES LIKE '".LIKERTM_USER_ANSWERS."'") != LIKERTM_USER_ANSWERS) {  
         $sql = "CREATE TABLE `".LIKERTM_USER_ANSWERS."` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`question_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`answer` TEXT NOT NULL, /* keep it text just in case but it will store the choice ID */					
					`points` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
					`taking_id` INT UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)
				) CHARACTER SET utf8;";
         $wpdb->query($sql);         
     } 
     
     likertm_add_db_fields(array(
        array("name"=>"name", "type"=>"VARCHAR(100) NOT NULL DEFAULT '' "),
        array("name"=>"email", "type"=>"VARCHAR(100) NOT NULL DEFAULT '' "),				
     ), LIKERTM_TAKINGS);
     
      likertm_add_db_fields(array(
        array("name"=>"ask_for_name", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
        array("name"=>"ask_for_email", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),				
     ), LIKERTM_SURVEYS);
     
     	update_option( "likertm_version", '0.71' );
}

function likertmaster_init() {
	global $wpdb;
	
	// define constants for table names, if any	
   define('LIKERTM_SURVEYS', $wpdb->prefix.'likertm_surveys');
	define('LIKERTM_QUESTIONS', $wpdb->prefix.'likertm_questions');
	define('LIKERTM_CHOICES', $wpdb->prefix.'likertm_choices');
	define('LIKERTM_QCATS', $wpdb->prefix.'likertm_qcats');
	define('LIKERTM_TAKINGS', $wpdb->prefix.'likertm_takings');
	define('LIKERTM_USER_ANSWERS', $wpdb->prefix.'likertm_user_answers');
	
   // add custom admin menu entries if any
	add_action('admin_menu', 'likertmaster_menu');
	add_action('wp_enqueue_scripts', 'likertm_enqueue_scripts');
	
	add_action('wp_ajax_likertm_ajax', 'likertm_ajax');
	add_action('wp_ajax_nopriv_likertm_ajax', 'likertm_ajax');
	
	// add custom shortcodes here. This will call the shortcode handler in controller/shortcodes.php
	add_shortcode('likertm', array('LikertMasterShortcodes', 'survey'));
	add_shortcode('likertm-barchart', array('LikertMasterShortcodes', 'barchart'));
	
	likertm_define_filters();
	
	$version = get_option('likertm_version');
	if($version != '0.71') likertmaster_activate(true);
	
	// add custom action handlers if any
	// add_action('watupro_completed_exam', array('WatuPROCustomActions', 'completed_exam'));
}

function likertmaster_menu() {
	 add_menu_page(__('Likert Survey Master', 'likertm'), __('Likert Survey Master', 'likertm'), 'manage_options', "likert_master", array('LikertMaster', 'create'));
	 add_submenu_page('likert_master', __('Create Survey', 'likertm'), __('Create Survey', 'likertm'), 'manage_options', "likert_master", array('LikertMaster', 'create'));
	 add_submenu_page('likert_master', __('Manage Surveys', 'likertm'), __('Manage Surveys', 'likertm'), 'manage_options', "likertm_surveys", array('LikertMaster', 'manage'));
	 add_submenu_page('likert_master', __('Question Categories', 'likertm'), __('Question Categories', 'likertm'), 'manage_options', "likertm_qcats", array('LikertQcats', 'manage'));
	 
	 // edit quiz
	 add_submenu_page(null, __('Edit Survey', 'likertm'), __('Question Categories', 'likertm'), 'manage_options', "likertm_survey", array('LikertMaster', 'edit'));
	 add_submenu_page(null, __('Manage Questions', 'likertm'), __('Manage Questions', 'likertm'), 'manage_options', "likertm_questions", array('LikertmQuestions', 'manage'));
	 add_submenu_page(null, __('View Answers', 'likertm'), __('View Answers', 'likertm'), 'manage_options', "likertm_results", array('LikertmResults', 'view'));
	 add_submenu_page(null, __('Stats Per Question', 'likertm'), __('Stats Per Question', 'likertm'), 'manage_options', "likertm_per_question", array('LikertmResults', 'per_question'));
}

// small redirect helper
function likertm_redirect($url) {
	echo "<meta http-equiv='refresh' content='0;url=$url' />"; 
	exit;
}

// enqueue scripts and CSS
function likertm_enqueue_scripts() {
	wp_enqueue_script('jquery');
        
   wp_enqueue_style(
			'likertm-style',
			LIKERTM_URL.'css/main.css',
			array(),
			'0.1');
			
   wp_enqueue_script(
			'likertm-script',
			LIKERTM_URL.'js/main.js',
			array(),
			'0.1');	
			
	$translation_array = array('ajax_url' => admin_url('admin-ajax.php'),
		'answering_required' => __('Answering this question is required.', 'likertm'),
		'please_enter_name' => __('Please enter your name', 'likertm'),
		'please_enter_email' => __('Please enter valid email address', 'likertm'),
		);
	wp_localize_script( 'likertm-script', 'likertm_i18n', $translation_array );					
}

// manually apply Wordpress filters on the content
// to avoid calling apply_filters('the_content')	
function likertm_define_filters() {
	global $wp_embed;
	
	add_filter( 'likertm_content', 'wptexturize' ); // Questionable use!
	add_filter( 'likertm_content', 'convert_smilies' );
   add_filter( 'likertm_content', 'convert_chars' );
	add_filter( 'likertm_content', 'shortcode_unautop' );
	add_filter( 'likertm_content', 'do_shortcode' );
}	

// function to conditionally add DB fields
function likertm_add_db_fields($fields, $table) {
		global $wpdb;
		
		// check fields
		$table_fields = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
		$table_field_names = array();
		foreach($table_fields as $f) $table_field_names[] = $f->Field;		
		$fields_to_add=array();
		
		foreach($fields as $field) {
			 if(!in_array($field['name'], $table_field_names)) {
			 	  $fields_to_add[] = $field;
			 } 
		}
		
		// now if there are fields to add, run the query
		if(!empty($fields_to_add)) {
			 $sql = "ALTER TABLE `$table` ";
			 
			 foreach($fields_to_add as $cnt => $field) {
			 	 if($cnt > 0) $sql .= ", ";
			 	 $sql .= "ADD $field[name] $field[type]";
			 } 
			 
			 $wpdb->query($sql);
		}
}
