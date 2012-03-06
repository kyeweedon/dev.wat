<?php
	require_once('/var/www/html/mech/php/all.php');
	
	// Define
	$reply = array();
	
	// If Super Coord
	if($_SESSION['wappRole'] == 1111) {
		$condition = 'u.companyID = ' . $_SESSION['companyID'] . ' AND (u.userID = j.creatorID OR u.userID = j.ownerID)';
	}
	// Else Coord
	else {
		$condition = '(j.creatorID = ' . $_SESSION['userID'] . ' OR j.ownerID = ' . $_SESSION['userID'] . ')';
	}
	
	require('dbConfig.php');
	
	// Get jobs accessible to this user
	$sql = '' .
		'SELECT DISTINCT '.
			'j.jobID, '.
			'j.status, '.
			'j.creatorID, '.
			'j.ownerID, '.
			'j.claimNumber, '.
			'CONCAT(j.locAddress, ", ", j.locSuburb, ", ", j.locPostCode)  AS "address", '.
			'CONCAT(j.firstName, " ", j.lastName) AS "claimant", '.
			'c.name AS "insurer", '.
			'j.dateCreated '.
		'FROM '.
			'Job j, '.
			'User u, '.
			'Company c '.
		'WHERE '.
			$condition . ' AND '.
			'j.insurerID = c.companyID ' .
		'ORDER BY ' .
			'dateCreated' .
		'';
			
	$jobs = mysql_query($sql) or die('error');
	
	// For each job returned
	while($row = mysql_fetch_assoc($jobs)) {
		
		// Determine age		
		$time = time() - $row['dateCreated'];
		$unit = '';
		if($time <  3600)                  { $unit = 'm'; $num = floor($time / 60    ); }
		if($time >= 3600 && $time < 86400) { $unit = 'h'; $num = floor($time / 3600  ); }
		if($time >= 86400)                 { $unit = 'd'; $num = floor($time / 86400 ); }
		$row['age'] = $num . $unit;
		
		// Add job to reply
		array_push($reply, $row);
	}
	//debug($sql);
	// Return jobs as json
	echo json_encode($reply);
?>