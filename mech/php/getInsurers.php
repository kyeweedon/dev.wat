<?php
	
	$reply = array();
	$count = 0;
	
	require('dbConfig.php');
	
	// Get all companies who are insurers
	$sql = 'SELECT companyID, name FROM Company WHERE isInsurer = 1 ORDER BY name';
	$insurers = mysql_query($sql) or die('error');
	
	// For each company returned
	while($row = mysql_fetch_assoc($insurers)) {
		
		// Add company to reply
		array_push($reply, $row);
		$count++;
	}
	
	$reply["length"] = $count;
	
	// Return Insurers as json
	echo json_encode($reply);
?>