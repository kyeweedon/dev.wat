<?php
	
	session_start();
	
	require_once('/var/www/html/mech/php/PhpConsole.php');
	require_once('/var/www/html/mech/php/dbConfig.php');	
	
	// Browser test
	$u_agent = $_SERVER['HTTP_USER_AGENT']; 
	if(preg_match('/Chrome/i', $u_agent) || preg_match('/Safari/i', $u_agent)) {} else {
		
		// Redirect to fail page
		debug('Browser fail.');
		header("Location: /mech/php/outdatedBrowser.php");	
		
	}
	
	// Check if authenticated
	if(!isset($_SESSION['status']) || $_SESSION['status'] === 'bad') {
		
		// Redirect to management page
		header("Location: /");
		
	}
	
?>