<?php
	
	require_once('/var/www/html/mech/php/all.php');
	
	$reply = array();
	$count = 0;
		
	// Get all Coordinators in my Co
	$sql = ''.
		'SELECT '.
			'u.userID, '.
			'CONCAT(u.firstName, " ", u.lastName) AS "name" '.
		'FROM '.
			'User u '.
		'WHERE '.
			'(u.wappRole = "1011" OR u.wappRole = "1111" OR u.wappRole = "0111") AND '.
			'u.companyID = ' . $_SESSION['companyID'] .
		'';
	
	$managers = mysql_query($sql) or die('error');
	
	// For each user returned
	while($row = mysql_fetch_assoc($managers)) {
		
		// Add user to reply
		array_push($reply, $row);
		$count++;
	}
	//debug($sql);
	
	$reply['length'] = $count;
	
	// Return Coordinators as json
	echo json_encode($reply);
?>