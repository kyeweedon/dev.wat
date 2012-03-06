<?php

	require_once('/var/www/html/mech/php/all.php');
	$reply;
	
	$sql = $_POST['query'];
	
	if($result = mysql_query($sql)) {
		
		$reply['status'] = 'good';
		
		if(mysql_num_rows($result) > 0) {
			
			$reply['result'] = mysql_fetch_assoc($result);
			
		}
	}
	else {
		
		$reply['status'] = 'bad';
		debug('Query Failed.');
		
	}
	
	// Return Inspectors as json
	echo json_encode($reply);
	
?>