<?php 

	require_once('/var/www/html/mech/php/all.php');
	require_once('/var/www/html/mech/php/class.ezpdf.php'); 
	
	// Get Job {
	$jobID  = $_SESSION['selectedJob'];
	
	$sql =
		'SELECT ' .
			'j.jobID, ' .
			'CONCAT(j.firstName, " ", j.lastName) AS "claimant", '.
			'j.locAddress AS address, ' .
			'j.locSuburb AS suburb, ' .
			'j.locPostCode AS postCode, '.
			'j.phoneMobile, ' .
			'j.phoneLandline, ' .
			'c.name AS insurer, '.
			'j.claimNumber, ' .
			'FROM_UNIXTIME(i.appointment) AS appointment, ' .
			'j.claimBrief, ' .
			'i.inspectionID, ' .
			'i.causationNotes, ' .
			'i.isScope, ' .
			'i.isCausation, ' .
			'CONCAT(u.firstName, " ", u.lastName) AS owner ' .
		'FROM '.
			'Job j, '.
			'User u, '.
			'Company c, '.
			'Inspection i ' .
		'WHERE '.
			'u.userID = j.ownerID AND ' .
			'c.companyID = j.insurerID AND ' .
			'j.jobID = i.jobID AND ' .
			'i.isCurrent = 1 AND ' .
			'j.jobID = ' . $jobID
		;
	
	// Try query
	if($result = mysql_query($sql)) {
		
		$job = mysql_fetch_assoc($result);
		$taskID = $job['inspectionID'];
		
	}
	else {
		debug('Failed getting Job Info: ' . $sql);	
	}
	// }
	
	$reply = 'bad';
	
	makeScope($job, $taskID);
	
	$reply = 'good';
	
	echo json_encode($reply);
	
	// Make Scope Report {
	function makeScope($job, $taskID) {
		
		debug('Making Scope');
		
		// Start Scope PDF creation
		$pdf = new Cezpdf();
		$pageCount = 0;
		$cursorY = 750;
		$pageTitle = "Scope of Works";
		
		$inspector = getInspectorInfo($taskID);
		$buildings = getBuildings($taskID);
		$locations = getLocations($taskID);
		$elements  = getElements($taskID);
		
		// Debug
		debug(count($buildings) . ' Buildings');
		debug(count($locations) . ' Locations');
		debug(count($elements)  . ' Elements' );
		
		// Build Content
		list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
		list($pdf, $pageCount, $cursorY) = buildContent($pdf, $pageCount, $pageTitle, $cursorY, 'scope', $buildings, $locations, $elements);
		
		// Display Report
		$cbuf = $pdf->output();
		$pdf->ezStream();
		
	} // }
	
	// Get Inspector Info {
	function getInspectorInfo($taskID) {
		
		$sql =
			'SELECT ' .
				'CONCAT(u.firstName, " ", u.lastName) AS "name", '.
				'u.email ' .
			'FROM '.
				'Inspection i, '.
				'User u ' .
			'WHERE '.
				'u.userID = i.assignedID AND ' .
				'i.inspectionID = ' . $taskID
			;
		
		// Try query
		if($result = mysql_query($sql)) {
			
			return mysql_fetch_assoc($result);
			
		}
		else {
			debug('Failed getting Inspector Info: ' . $sql);	
		}
		
	}
	// }
	
	// Get Buildings {
	function getBuildings($taskID) {
		
		$sql =
			'SELECT ' .
				'b.buildingID, ' .
				'bn.name, ' .
				'b.notes, ' .
				'br.name AS roof, ' .
				'CONCAT(bt.category, " ", bt.name) AS type, ' .
				'bc.name AS cladding, ' .
				'b.age, ' .
				'bp.name AS period, ' .
				'bf.name AS floor, ' .
				'ba.name AS aspect, ' .
				'bs.name AS slope ' .
			'FROM '.
				'Building b, ' .
				'BuildingName bn, ' .
				'BuildingRoof br, ' .
				'BuildingType bt, ' .
				'BuildingFloor bf, ' .
				'BuildingSiteSlope bs, ' .
				'BuildingAspect ba, ' .
				'BuildingCladding bc, ' .
				'BuildingPeriod bp ' .
			'WHERE '.
				'b.inspectionID = ' . $taskID . ' AND ' .
				'b.nameID = bn.buildingNameID AND ' .
				'b.roofID = br.buildingRoofID AND ' .
				'b.typeID = bt.buildingTypeID AND ' .
				'b.floorID = bf.buildingFloorID AND ' .
				'b.aspectID = ba.buildingAspectID AND ' .
				'b.claddingID = bc.buildingCladdingID AND ' .
				'b.siteSlopeID = bs.buildingSiteSlopeID AND ' .
				'b.periodID = bp.buildingPeriodID AND ' .
				'b.isLive = 1'
			;
		
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Building
			while($building = mysql_fetch_assoc($result)) {
				
				array_push($return, $building);
				
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Buildings: ' . $sql);	
		}
		
	}
	// }
	
	// Get Locations {
	function getLocations($taskID) {
	
		$sql =
			'SELECT ' .
				'l.locationID, ' .
				'l.buildingID, ' .
				'lr.name, ' .
				'l.length, ' .
				'l.width, ' .
				'l.height, ' .
				'l.notes ' .
			'FROM '.
				'Building b, ' .
				'Location l, ' .
				'LocationRoom lr ' .
			'WHERE '.
				'b.inspectionID = ' . $taskID . ' AND ' .
				'l.buildingID = b.buildingID AND ' .
				'l.roomID = lr.locationRoomID AND ' .
				'l.isLive = 1'
			;
		//debug($sql);
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Location
			while($location = mysql_fetch_assoc($result)) {
				
				array_push($return, $location);
			
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Locations: ' . $sql);	
		}
		
	}// }
	
	// Get Elements {
	function getElements($taskID) {
		
		$sql =
			'SELECT ' .
				'e.elementID, ' .
				'e.locationID, ' .
				'lr.name AS locationName, ' .
				'et.category, ' .
				'et.name, ' .
				'et.attribute, ' .
				'e.notes, ' .
				'e.paintQty, ' .
				'e.areaAffected, ' .
				'u.short AS units, ' .
				'e.isPAP, ' .
				'e.isReinstall, ' .
				'e.isRemove, ' .
				'e.isRepair, ' .
				'e.isSAI, ' .
				'e.isCleanup, ' .
				'd.name AS damage, ' .
				'ev.name AS event ' .
			'FROM ' .
				'Building b, ' .
				'Location l, ' .
				'LocationRoom lr, ' .
				'Element e, ' .
				'ElementType et, ' .
				'Units u, ' .
				'Damage d, ' .
				'Event ev ' .
			'WHERE '.
				'b.inspectionID = ' . $taskID . ' AND ' .
				'l.buildingID = b.buildingID AND ' .
				'e.locationID = l.locationID AND ' .
				'l.roomID = lr.locationRoomID AND ' .
				'e.elementTypeID = et.elementTypeID AND ' .
				'e.damageID = d.damageID AND ' .
				'e.eventID = ev.eventID AND ' .
				'et.unitsID = u.unitsID AND ' .
				'l.isLive = 1'
			;
		//debug($sql);
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Element
			while($element = mysql_fetch_assoc($result)) {
				
				array_push($return, $element);
			
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Elements: ' . $sql);	
		}
		
	} // }
	
	// Create New Page {
	function addNewPage($pdf, $pageCount, $pageTitle) {
	
		// If after first page
		if ($pageCount >= 1) {
			
			// Create blank
			$pdf->ezNewPage(); 
			$cursorY = 760;
			
		}
		$pageCount++;
		
		// Background
		$pdf->setColor(0.87,0.87,0.85);
		$pdf->filledRectangle(0,0,595,842);
	
		// White insert
		$pdf->setColor(1,1,1);
		$pdf->filledRectangle(25,80,545,700);
		$pdf->filledEllipse( 35, 80, 10, 0, 0, 8, 0, 360);
		$pdf->filledEllipse( 560, 80, 10, 0, 0, 8, 0, 360);
		$pdf->filledRectangle(35,70,525,20);
		$pdf->filledEllipse( 35, 780, 10, 0, 0, 8, 0, 360);
		$pdf->filledEllipse( 560, 780, 10, 0, 0, 8, 0, 360);
		$pdf->filledRectangle(35,770,525,20);
	
		// Header
		$pdf->addPngFromFile('/src/images/logoSmall.png', 45, 800, 90);
		
		// Page Numbers
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(550, 800, 10, $pageCount);
		
		// Footer
		$pdf->setColor(0.6,0.6,0.6);
		$pdf->selectFont('./fonts/Helvetica.afm');
		$copy = utf8_decode("©");
		$pdf->addText(343, 55, 8, $copy." 2012 Watson Inc.");	
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->addText(418, 55, 8, "103 Dobell Dr, Wangi Wangi, NSW 2267");
		$pdf->addText(264, 45, 8, "Interested in getting your own custom reports? Contact Watson for more information.");
	
		// For page 1
		if ($pageCount <= 1) {
			
			// Set Report Title
			$pdf->setColor(0.23,0.22,0.22);
			$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
			if ($pageTitle == "Scope of Works") {
				
				$pdf->addText(200, 750, 24, "SCOPE OF WORKS");
				
			} else {
				
				$pdf->addText(200, 750, 24, "CAUSATION REPORT");
				
			}
			$cursorY = 750;
		}
		
		return array ($pdf, $pageCount, $cursorY);
			
	} // }
	
	// Build Content {
	function buildContent($pdf, $pageCount, $pageTitle, $cursorY, $type, $buildings, $locations, $elements) {
		
		foreach($buildings as $Bk => $Bv) {
			
			// Draw Building
			if ($cursorY <= 250) {
	        	list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
			}
			$cursorY = addBuilding($pdf, $cursorY, $Bv);
			
			// Per Location
			foreach($locations as $Lk => $Lv) {
				
				// If belongs here
				if($Lv['buildingID'] == $Bv['buildingID']) {
					
					// Draw Location
					if ($cursorY <= 120) {
						list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
					}
					$cursorY = addLocation($pdf, $cursorY, $Lv);
					
					// Per Element
					foreach($elements as $Ek => $Ev) {
						
						// If belongs here
						if($Ev['locationID'] == $Lv['locationID']) {
							
							// Draw Element
							if ($cursorY <= 150) {
								list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
							}
							$cursorY = addScopeElement($pdf, $cursorY, $Ev);
							
						}
						
					}
					
				}
				
			}
			
		}
		
		return array($pdf, $pageCount, $cursorY);
		
	}
	// }
	
	// Add Building {
	function addBuilding($pdf, $cursorY, $building) {
		
		// Name
		$cursorY -= 50;
		$building['name'] = strtoupper($building['name']);
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
		$pdf->addText(45, $cursorY, 18, $building['name']);
	
		// Notes
		
		$pdf->selectFont('./fonts/Helvetica.afm');
		$lines = explode("\n",$building['notes']); 
		foreach($lines as $line){ 
			
	    	$foo = $line;
	    	$foo = wordwrap($foo,110,"|");
	    	$Arrx = explode("|",$foo);
	    	$i = 0;
	    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
	    		
	    		$pdf->addText(45, $cursorY-15, 10, $Arrx[$i]);
	       		//PDF_show_xy($pdf,$Arrx[$i], 45, $cursorY-15);
	       		$i++;
	       		$cursorY -= 10;
	       		
	    	}
		}
		
		// Roof
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 30, 12, "ROOF");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['roof']);
		
		// Type
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(300, $cursorY, 12, "TYPE");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(385, $cursorY, 12, $building['type']);
		
		// Cladding
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 15, 12, "CLADDING");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['cladding']);
		
		// Age
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(300, $cursorY, 12, "AGE");
		$pdf->selectFont('./fonts/Helvetica.afm');
		if($building['age'] == '0') {
			
			$pdf->addText(385, $cursorY, 12, $building['period']);
			
		}
		else {
			
			$pdf->addText(385, $cursorY, 12, $building['age']);
			
		}
		
		// Floor
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 15, 12, "FLOOR");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['floor']);
		
		// Aspect
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(300, $cursorY, 12, "ASPECT");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(385, $cursorY, 12, $building['aspect']);
		
		// Slope
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 15, 12, "SITE SLOPE");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['slope']);
		
		$cursorY -= 20;
	
		return $cursorY;
	
	} // }
	
	// Add Location {
	function addLocation($pdf, $cursorY, $location) {
		
		// Bounds box start
		$boundsBoxStart = $cursorY;
		
		// { Determine height
		$cursorY -=25; // Name
		
		if($location['notes'] != '') {
			
			$lines = explode("\n",$location['notes']); 
			foreach($lines as $line){ 
				
		    	$foo = $line;
		    	$foo = wordwrap($foo,110,"|");
		    	$Arrx = explode("|",$foo);
		    	$i = 0;
		    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
		    		
		       		$i++;
		       		$cursorY -= 10;
		       		
		    	}
		    	
			}
			
		}
		// } Determine height
		
		// Bounds box build
		$boundsBoxHeight = $boundsBoxStart - $cursorY + 5;
		
		$pdf->setColor(0.87,0.87,0.85);
		$pdf->filledRectangle(35, $boundsBoxStart - $boundsBoxHeight, 525, $boundsBoxHeight);
		
		// Return to top of bounds box
		$cursorY = $boundsBoxStart;
		
		// Name
		$location['name'] = strtoupper($location['name']);
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
		$pdf->addText(45, $cursorY-=20, 15, $location['name']);
		
		// Dimensions
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(440, $boundsBoxStart - ($boundsBoxHeight / 2) - 5, 10, $location['length']." x ".$location['width']." x ".$location['height']."mm");	
		$cursorY+=1;
		
		// Notes
		$pdf->setColor(0.30,0.30,0.30);
		if($location['notes'] != '') {
			foreach($lines as $line){ 
				
		    	$foo = $line;
		    	$foo = wordwrap($foo,110,"|");
		    	$Arrx = explode("|",$foo);
		    	$i = 0;
		    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
		    		
		    		$pdf->addText(45, $cursorY-15, 10, $Arrx[$i]);
		       		$i++;
		       		$cursorY -= 10;
		       		
		    	}
		    	
			}
		}
		
		$cursorY -= 12;
	
		return $cursorY;
		
	} // }
	
	// Add Element (Scope) {
	function addScopeElement($pdf, $cursorY, $element) {
		
		$pdf->setColor(0.2,0.2,0.2);
		$pdf->setLineStyle(1);
		$pdf->line(35,$cursorY,560,$cursorY);
		
		// Category
		$pdf->setColor(0.2,0.2,0.2);
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(45, $cursorY-=20, 12, $element['category']);
		$right=$pdf->getTextWidth(12, $element['category']) + 50;
		
		// { Name
		
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		
		// { If room on this line
		if($right + $pdf->getTextWidth(10, $element['name']) + 5 < 370) {
			
			$pdf->addText($right, $cursorY, 12, $element['name']);
			$right+=$pdf->getTextWidth(12, $element['name']) + 5;
		
		}
		// } If room on this line
		// { Else
		else {
			
			$pdf->addText(45, $cursorY-=18, 12, $element['name']);
			$right=45+$pdf->getTextWidth(12, $element['name']) + 5;
			$elHeight+=18;
			
		}
		// } Else
		
		// } Name
		
		// { Attribue
		
		$pdf->selectFont('./fonts/Helvetica.afm');
		
		// { If room on this line
		if($right + $pdf->getTextWidth(10, $element['attribute']) + 5 < 370) {
			
			$pdf->addText($right, $cursorY, 10, '<i>' . $element['attribute'] . '</i>');
		
		}
		// } If room on this line
		// { Else
		else {
			
			$pdf->addText(45, $cursorY-=15, 10, '<i>' . $element['attribute'] . '</i>');
			$elHeight+=15;
			
		}
		// } Else
		
		// } Attribute
		
		// Paint Qty
		if ($element['paintQty'] != '0') {
			$pdf->selectFont('./fonts/Helvetica.afm');
			$pdf->addText(370, $cursorY, 12, "Paint - ".$element['paintQty']." ".$element['units']);
		}
		
		// Quantity
		$pdf->selectFont('./fonts/Helvetica.afm');
		$right=$pdf->getTextWidth(12, $element['areaAffected']." ".$element['units']);
		$pdf->addText(550-$right, $cursorY, 12, $element['areaAffected']." ".$element['units']);
	
		// { Rectifications
		$pdf->setColor(0.4,0.4,0.4);
		if ($element['isRemove'] == 1) {
			if (($element['isSAI'] == 1) || ($element['isPAP'] == 1) || ($element['isRepair'] == 1) || ($element['isReinstall'] == 1)) {
			$isRm = "Remove, ";
			} else {	
			$isRm = "Remove";
			}
		} else {
		$isRm = "";
		}
		
		if ($element['isSAI'] == 1) {
			if (($element['isPAP'] == 1) || ($element['isRepair'] == 1) || ($element['isReinstall'] == 1)) {
				$isSpply = "Supply & Install, ";
				} else {
				$isSpply = "Supply & Install";
			}
		} else {
		$isSpply = "";
		}
		
		if ($element['isPAP'] == 1) {	
		if (($element['isRepair'] == 1) || ($element['isReinstall'] == 1)) {
		$isPrep = "Prepare & Paint, ";
		} else {
		$isPrep = "Prepare & Paint";
		}
		} else {
		$isPrep = "";
		}
		
		if ($element['isRepair'] == 1) {
		if ($element['isReinstall'] == 1) {
		$isRp = "Repair, ";
		} else {
		$isRp = "Repair";
		}
		} else {
		$isRp = "";
		}
		
		if ($element['isReinstall'] == 1) {
		$isRi = "Reinstall";
		} else {
		$isRi = "";
		}
		// } Rectifications
		
		$action = $isRm." ".$isSpply." ".$isPrep." ".$isRp." ".$isRi;
		$pdf->addText(45, $cursorY-=15, 11, $action);
		
		// { Notes
		$pdf->setColor(0.6,0.6,0.6);
		if ($element['notes'] != '') {
			$lines = explode("\n",$element['notes']); 
			foreach($lines as $line){ 
				
		    	$foo = $line;
		    	$foo = wordwrap($foo,110,"|");
		    	$Arrx = explode("|",$foo);
		    	$i = 0;
		    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
		    		
		    		$pdf->addText(45, $cursorY-15, 10, $Arrx[$i]);
		       		$i++;
		       		$cursorY -= 10;
		       		
		    	}
		    	
			}
		}
		// } Notes
		
		$cursorY -= 13;
		
		return $cursorY;
	
	} // }
	
?>