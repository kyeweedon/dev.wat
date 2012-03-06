<?php
	
	require_once('/var/www/html/mech/php/PhpConsole.php');
	require_once('/var/www/html/mech/php/dbConfig.php');
	session_start();
	
	// Create reply
	$reply = 'bad';
	
	// Pull username & password
	$un = $_POST['userName'];
	$pw = $_POST['passWord'];
	
	// Build query
	$sql = ''                                 .
		'SELECT '                             .
			'u.userID, '                      .
			'u.userName, '                    .
			'u.firstName, '                   .
			'u.lastName, '                    .
			'u.email, '                       .
			'u.wappRole, '                    .
			'c.companyID, '                   .
			'c.name, '                        .
			'c.isInsurer, '                   .
			'c.hasInspector, '                .
			'c.hasEstimator '                 .
		'FROM '                               .
			'User u, '                        .
			'Company c '                      .
		'WHERE '                              .
			'u.companyID = c.companyID AND '  .
			'u.userName = "' . $un . '" AND ' .
			'u.passWord = "' . $pw . '"'      .
	'';
	
	// Try query
	if($result = mysql_query($sql)) {
		
		// If credentials are valid (1 record returned)
		if(mysql_num_rows($result) == 1) {
			
			// Parse results as array
			$result = mysql_fetch_assoc($result);
			
			// Good login
			$reply = 'good';
			
			// Build session
			$_SESSION['status']       = 'good'                 ;
			$_SESSION['userID']       = $result['userID']      ;
			$_SESSION['userName']     = $result['userName']    ;
			$_SESSION['firstName']    = $result['firstName']   ;
			$_SESSION['lastName']     = $result['lastName']    ;
			$_SESSION['email']        = $result['email']       ;
			$_SESSION['wappRole']     = $result['wappRole']    ;
			$_SESSION['companyID']    = $result['companyID']   ;
			$_SESSION['companyName']  = $result['name']        ;
			$_SESSION['isInsurer']    = $result['isInsurer']   ;
			$_SESSION['wappRole']     = $result['wappRole']    ;
			$_SESSION['hasInspector'] = $result['hasInspector'];
			$_SESSION['hasEstimator'] = $result['hasEstimator'];
		}
	
	}
	else {
		
		debug($sql);

	}
	
	$_SESSION['status'] = $reply;
	
	
	echo $reply;
?>