<?php	
	
	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
	require_once('/var/www/html/mech/php/PhpConsole.php');
	
	function authenticate($un, $pw) {
		
		require('/var/www/html/mech/php/dbConfig.php');
		
		// Build query
		$sql = ''                               .
			'SELECT '                           .
				'userID, '                      .
				'firstName, '                   .
				'lastName '                     .
			'FROM '                             .
				'User '                         .
			'WHERE '                            .
				'userName = "' . $un . '" AND ' .
				'passWord = "' . $pw . '"'      .
			'';
		//debug($sql);
		// Ensure successful query
		if($result = mysql_query($sql)) {

			// If credentials are valid (1 record returned)
			if(mysql_num_rows($result) == 1) {
				
				$user = mysql_fetch_assoc($result);
				return $user;
				
			}
			// Invalid credentials
			else {
				
				return false;
				
			}
			
		}
		// Failed query
		else {
			
			debug($sql);
			return false;
			
		}		
	}
    
?>