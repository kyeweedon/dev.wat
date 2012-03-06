<?php
	session_start();
	
	// Only accessible locally by user in session
	if(isset($_SESSION["userID"])) {
		$result['status'] = 'good';
		
		/*
		$result['userID']      = $_SESSION['userID'];
		$result['companyID']   = $_SESSION['companyID'];
		$result['companyName'] = $_SESSION['companyName'];
		$result['isInsurer']   = $_SESSION['isInsurer'];
		$result['userName']    = $_SESSION['userName'];
		$result['firstName']   = $_SESSION['firstName'];
		$result['lastName']    = $_SESSION['lastName'];
		$result['email']       = $_SESSION['email'];
		$result['wappRole']    = $_SESSION['wappRole'];
		*/
		
		$result['data'] = $_SESSION;
	}
	else {
		$result['status'] = 'bad';
	}
   
	echo json_encode($result);
?>