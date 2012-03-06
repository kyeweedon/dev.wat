<?php

	require_once('/var/www/html/mech/php/all.php');
	require_once('/var/www/html/mech/php/functions.php');
	
	// Resolve TaskID
	$sql = 'SELECT * FROM Inspection WHERE jobID = ' . $_POST['jobID'] . ' AND isCurrent = 1';
	$result = mysql_query($sql);
	$result = mysql_fetch_assoc($result);
	$taskID = $result['inspectionID'];
	
	if(changeJobStatus($taskID, $_POST['status'])) {
	
		$reply['status'] = 'good';
		
	}
	else {
		
		$reply['status'] = 'bad';
		
	}
	
	echo json_encode($reply);

?>