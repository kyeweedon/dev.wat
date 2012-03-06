<?php
	// Set database info
    $dbHost = 'localhost';
    $dbUsername = 'watsonInternal';
    $dbPassword = 'evenrobotsneedpasswords';
    $dbName = 'WATSON';
    
    // Build db connection
    $dbConnection = mysql_connect($dbHost, $dbUsername, $dbPassword) or die('Bad DB Connect: ' . mysql_error());
    mysql_select_db($dbName) or die('Bad DB Select: ' . mysql_error());
?>