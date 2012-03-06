<?php
	//require_once('/var/www/html/mech/php/all.php');
	//debug('Setting selected Job');
	
	session_start();
	
	$reply = array();
	$reply['status'] = 'bad';
	
	// Only accessible locally by user in session
	if(isset($_SESSION["userID"])) {
      
      $key = $_POST['key'];
      
      $_SESSION[$key] = $_POST['value'];
      
      $reply['status'] = 'good';
	}
	
	echo json_encode($reply);
	
?>