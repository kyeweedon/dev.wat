<?php

	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
	session_start();
	$_SESSION = array();
	session_destroy();
	header('Location: /');
?>