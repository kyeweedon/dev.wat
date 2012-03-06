<?php
	require_once('/var/www/mech/php/all.php');
	
	// Define
	$reply = array();
	
	require('dbConfig.php');
	
	// Get current task for this Job
	$sql = '' .
		'SELECT '.
			'* ' .
		'FROM '.
			'Inspection ' .
		'WHERE '.
			'jobID = ' . $_SESSION['selectedJob'] .
		'';
			
	if($job = mysql_query($sql)) {
	
		// Add task to reply
		$reply = mysql_fetch_assoc($job);
		$reply['status'] = 'good';
		
		// Build Buildings
		
		// Build Locations
		
		// Build Elements
	}
	else {
		$reply['status'] = 'bad';
	}
   
   echo json_encode($reply);
?>