<?php
	
	session_start();
	
	$reply = array();
	
	require('dbConfig.php');
	
	// Get all companies who are insurers
	$sql = ''.
	/* For externals
		'SELECT ' .
			'c.companyID AS "inspectorID", '.
			'c.name, '.
			'1 AS "isExternalCompany" '.
		'FROM '.
			'Company c '.
		'WHERE '.
			'c.hasInspector = 1 '.
		'UNION '.
	*/
		'SELECT '.
			'u.userID, '.
			'CONCAT(u.firstName, " ", u.lastName) AS "name" '.
			//'0 AS "isExternalCompany" '.
		'FROM '.
			'User u '.
		'WHERE '.
			'(u.mappRole = "1101" OR u.mappRole = "1111" OR mappRole = "0111") AND '.
			'u.companyID = ' . $_SESSION['companyID'] . ' ' .
		//'ORDER BY '.
		  //'"isExternalCompany"'.
	'';
	
	$inspectors = mysql_query($sql) or die('error');
	
	// For each company returned
	while($row = mysql_fetch_assoc($inspectors)) {
		
		// Add company to reply
		array_push($reply, $row);
	}
		
	// Return Inspectors as json
	echo json_encode($reply);
?>