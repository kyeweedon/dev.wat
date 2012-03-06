<?php
	
	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
	require_once('/var/www/html/mech/php/PhpConsole.php');
	require_once('/var/www/html/mech/php/dbConfig.php');
	require_once('/var/www/html/app/authenticate.php');
	
	// ================
	{  // FUNCTIONS
	
	// Build reply per table
	function buildTable($table) {
		
		// Setup return
		$return = array();
		
		// Build query
		switch($table) {
			
			case 'BuildingPeriod': {
				$sql = '' .
					'SELECT ' .
						'buildingPeriodID AS "serverID", ' .
						'name, '.
						'yearFrom AS fromDate, ' .
						'yearTo AS toDate, ' .
						'isLive ' .
					'FROM ' .
						'BuildingPeriod' .
				'';
				break;
			}
			
			case 'Damage': {
				$sql = '' .
					'SELECT ' .
						'dcl.damageCategoryLinkID AS "serverID", ' .
						'dc.name AS "category", '.
						'd.name, ' .
						'd.isLive ' .
					'FROM ' .
						'Damage d, ' .
						'DamageCategory dc, ' .
						'DamageCategoryLink dcl ' .
					'WHERE ' .
						'dcl.damageID = d.damageID AND ' .
						'dcl.categoryID = dc.damageCategoryID'
				;
				break;
			}
			
			case 'ElementType': {
				$sql =
					'SELECT ' .
						'et.elementTypeID AS serverID, ' .
						'et.name, ' .
						'et.lastChanged AS lastUpdate, ' .
						'et.isLive, ' .
						'et.isCustom, ' .
						'dc.name AS damageCategory, ' .
						'et.category, ' .
						'et.attribute AS attribute1, ' .
						'et.unitsID AS unitID ' .
					'FROM ' . 
						'ElementType et, ' .
						'DamageCategory dc ' .
					'WHERE ' .
						'et.damageCategoryID = dc.damageCategoryID AND ' .
						'et.isCustom = 0'
				;
				break;
			}
			
			default: {
				$sql = 'SELECT *, ' . lcfirst($table) . 'ID AS "serverID" FROM ' . $table . ' ';
				break;
			}
		}
		//debug($sql);
		//echo $sql;
		// Try query
		if($results = mysql_query($sql)) {
			
			// Per record
			while($record = mysql_fetch_assoc($results)) {
				
				// Remove xID
				if(isset($record[lcfirst($table) . 'ID'])) {
					unset($record[lcfirst($table) . 'ID']);
				}
				
				// Add to return
				array_push($return, $record);
			}
			
		}
		
		return $return;
	}
	
	}  // FUNCTIONS
	// ================
	
	// ============
	{  // DEBUG
	
	if(!isset($_POST['json'])) {
		$_POST['json'] = '{' .
			'"userName":"dave", ' .
			'"password":"watson3283" ' .
		'}';
		debug($_POST['json']);
	}
	}  // DEBUG
	// ============
	
	// ===========
	{  // MAIN
	
	$json = json_decode($_POST['json'], true);
	$reply;
	$reply['connectionType'] = 'getAppData';
	
	// If credentials are valid
	if($user = authenticate($json['userName'], $json['password'])) {
		debug('Authentication successful');
		
		// Build replies
		$reply['Aspect']            = buildTable('BuildingAspect')   ;
		$reply['BuildingCondition'] = buildTable('BuildingCondition');
		$reply['SiteSlope']         = buildTable('BuildingSiteSlope');
		$reply['Cladding']          = buildTable('BuildingCladding') ;
		$reply['Flooring']          = buildTable('BuildingFloor')    ;
		$reply['BuildingName']      = buildTable('BuildingName')     ;
		$reply['Period']            = buildTable('BuildingPeriod')   ;
		$reply['Roofing']           = buildTable('BuildingRoof')     ;
		$reply['BuildingType']      = buildTable('BuildingType')     ;
		$reply['Damage']            = buildTable('Damage')           ;
		$reply['Event']             = buildTable('Event')            ;
		$reply['RoomName']          = buildTable('LocationRoom' )    ;
		$reply['Unit']              = buildTable('Units')            ;
		$reply['ElementDetail']     = buildTable('ElementType')      ;
	}
	
	// Credentials invalid
	else {
		$reply['status'] = 'bad';
		$reply['msg']    = 'Invalid authentication';
	}
	
	echo json_encode($reply);
	
	}  // MAIN
	// ===========
	
?>