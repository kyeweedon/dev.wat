<?php
	require_once('/var/www/html/mech/php/all.php');
	//debug('here');
	$reply = array();
	
	//debug('addJob.php loaded...');
	
	$reply['status'] = 'bad';
	
	// Define
	$userID = $_SESSION['userID'];
	
	// { Convert report checks
	$causation = 0;
	$scope     = 0;
	$costing   = 0;
	if($_POST['causation'] == 'true') {
		
		$causation = 1;
		
	}
	if($_POST['scope'] == 'true') {
		
		$scope = 1;
		
	}
	if($_POST['costing'] == 'true') {
		
		$costing = 1;
		
	}
	// } Convert report checks
	
	$sql = '' .
		'INSERT INTO Job SET '  .
			'status = "0.1", '  .
			'creatorID = '      . $_SESSION['userID']     . ', '  .
			'ownerID = '        . $_POST['ownerID']       . ', '  .
			'insurerID = '      . $_POST['insurerID']     . ', '  .
			'claimNumber = "'   . $_POST['claimNumber']   . '", ' .
			'firstName = "'     . $_POST['firstName']     . '", ' .
			'lastName = "'      . $_POST['lastName']      . '", ' .
			'locAddress = "'    . $_POST['locAddress']    . '", ' .
			'locSuburb = "'     . $_POST['locSuburb']     . '", ' .
			'locPostCode = "'   . $_POST['locPostCode']   . '", ' .
			'phoneMobile = "'   . $_POST['phoneMobile']   . '", ' .
			'phoneLandline = "' . $_POST['phoneLandline'] . '", ' .
			'claimBrief = "'    . $_POST['claimBrief']    . '", ' .
			'wantsCausation = ' . $causation              . ', '  .
			'wantsScope = '     . $scope                  . ', '  .
			'wantsCosting = '   . $costing                . ', '  .
			'dateCreated = '    . time()                  . 
		'';
	//debug('Query defined...');
	//debug($sql);
	
	if(mysql_query($sql)) {
	   //debug('Job added!');
	   $reply['status'] = 'good';
	   
	   // Get ID
	   $reply['jobID'] = mysql_insert_id();
	}
	else {
		debug('Failed to add Job');
	}

	// Return
	echo json_encode($reply);
?>