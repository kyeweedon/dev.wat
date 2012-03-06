var job;
var task;
var buildings;
var locations;
var elements;
var elementImages;
var inspectors;
var id;
	
// ==================
// Initialisers {
$(document).ready(function() {
	
	initialiseAll();

	// Preloader {
	var str =
		'<img src="/src/images/avatars/defaultGreen.png" />' +
		'<img src="/src/images/icons/homeGreen.png" />'      +
		'<img src="/src/images/icons/gearsGreen.png" />'     +
		'<img src="/src/images/icons/bookBlack.png" />'      +
		'<img src="/src/images/icons/docBlack.png" />'       +
		'<img src="/src/images/icons/cameraBlack.png" />'    ;	
	preload(str); // }
	
	// Get Inspectors
	getInspectors();
	
	initialise();
	
});	

function initialise() {
	
	job = null;
	task = null;
	buildings = null;
	locations = null;
	elements = null;
	elementImages = null;
	id = null;

	// Get Drilldown data {
	$.post('/mech/php/getDrilldownData.php',
		
		function(data) {
		
			if(data === null || data.status === 'bad') {
				alert('Error getting data.');
			}
			else {
					
				// Load into local holder
				job       = data.job      ;
				task      = data.task     ;
				buildings = data.buildings;
				locations = data.locations;
				elements  = data.elements ;
				
				console.log('> SUCCESS Get Drilldown Data');
				drawPage();
				
			}
			
		}
		
	); // }
	
}

// Initialisers }
// ==================

// ==============
// Dynamics {

// Doc Icon {
$('.iconDoc').live({
	
	mouseenter:function() {
		
		// Icon
		$(this).attr('src', '/src/images/icons/docBlack.png');
		
		// Open Popup
	},
	
	mouseleave:function() {
		
		// Icon
		$(this).attr('src', '/src/images/icons/docGrey.png');
		
		// Close Popup
	}
}); // }

// Camera Icon {
$('.iconCamera').live({
	
	mouseenter:function() {
		
		// Icon
		$(this).attr('src', '/src/images/icons/cameraBlack.png');
		
		// Open Popup
	},
	
	mouseleave:function() {
		
		// Icon
		$(this).attr('src', '/src/images/icons/cameraGrey.png');
		
		// Close Popup
	},
	
	click:function() {
		
		$('#modal' + $(this).attr('tag')).dialog({
			
			width:$(window).width() - 60,
			height:$(window).height() - 60,
			modal:true,
			show:'slide'
			
		}).children().show();
		
		if($(window).width() > $('#modal' + $(this).attr('tag')).children().first().width() + 60) {
			$('#modal' + $(this).attr('tag')).parent().width($('#modal' + $(this).attr('tag')).children().first().width() + 60);
			$('#modal' + $(this).attr('tag')).parent().css('margin-left', ($(window).width() / 2) - ($('#modal' + $(this).attr('tag')).parent().width() / 2));
		}
		
		if($(window).height() > $('#modal' + $(this).attr('tag')).children().first().height() + 60) {
			$('#modal' + $(this).attr('tag')).parent().height($('#modal' + $(this).attr('tag')).children().first().height() + 60);
		}
		
	}
	
}); // }

// Action Bar - More - Resubmit {
$('#resubmit').live({
	
	click:function() {
	
		// Confirm Resubmission
		$('#modalHead').html('Resubmit task...');
		$('#modalBox').html(
			'This Task will be sent back to ' + task.assignedName + '<br/>' +
			'with a note you enter below:' +
			'<textarea id="resubNote"></textarea>' +
			'Click "Send" to resubmit.'
		);
		$('#modal').append('<a class="modalButton" id="modalButtonYes">Send</a>');
		$('#modal').append('<a class="modalButton" id="modalButtonNo">Cancel</a>');
		$('#modal').attr('tag', 'resubmitTask');
		$('#modal').show();
		
	}
	
}); // }

// Modal - No {
$('#modalButtonNo').live({
	
	click:function() {
		
		$('#modalBack').fadeOut();
		
	}
	
}); // }

// Modal - Yes {
$('#modalButtonYes').live({
	
	// Click listener {
	click:function() {
		
		// Per Modal Type {
		switch($('#modal').attr('tag')) {
		
			case 'assignTask':   // {
				
				// Create Task
				addNewInspection(job.jobID, $('#modalPicker').val(), job.wantsCausation, job.wantsScope);
				
				// Reset & close
				$('#modal').html('<div id="modalHead"></div><div id="modalBox"></div>');
				$('#modal').hide();
				initialise();
					
				break;
			// }
			
			case 'reassignTask': // {
				
				// If current task owner selected
				if(task.assignedID === $('#modalPicker').val()) {
					
					$('#modalPicker').effect('highlight', { color:'#F00' }, 500);
					
				}
				else {
			
					// Create Task
					addNewInspection(job.jobID, $('#modalPicker').val(), job.wantsCausation, job.wantsScope);
								
					// Set isCancelled {
					$.post('/mech/php/setSingle.php',
						{
							table:'Inspection',
							key:'isCancelled',
							value:1,
							index:'inspectionID',
							selector:task.inspectionID
						},
						
						function(data) {
						
							if(data === null || data.status === 'bad') {
								alert('Error disabling Inspection.');
							}
							else {
								
								console.log('> SUCCESS isCancelled updated');
							
							}
							
						}
						
					); // }
					
					// Set isCurrent   {
					$.post('/mech/php/setSingle.php',
						{
							table:'Inspection',
							key:'isCurrent',
							value:0,
							index:'inspectionID',
							selector:task.inspectionID
						},
						
						function(data) {
						
							if(data === null || data.status === 'bad') {
								alert('Error disabling Inspection.');
							}
							else {
								
								console.log('> SUCCESS isCurrent updated');
								
								// Reset & close
								$('#modal').html('<div id="modalHead"></div><div id="modalBox"></div>');
								$('#modal').hide();
								initialise();
								
							}
						}
					); // }
				
				}
				
				break;
			// }
			
			case 'approveTask':  // {
				
				// Set isCompleted {
				$.ajax( {
					url:'/mech/php/setSingle.php',
					error:function(xhr, txt, err) {
						alert('Error setting single\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
					},
					timeout:5000,
					data: {
						table:'Inspection',
						key:'isCompleted',
						value:1,
						index:'inspectionID',
						selector:task.inspectionID
					},
					success:function(data) {
					
						if(data === null || data.status === 'bad') {
							alert('Error setting isCompleted.');
						}
						else {
							
							console.log('> SUCCESS isCompleted updated');
							
						}
					}
				}); // }
				
				// Set Job Status {
				$.ajax( {
					url:'/mech/php/setJobStatus.php',
					error:function(xhr, txt, err) {
						alert('Error setting single\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
					},
					timeout:5000,
					data: {
						status:'9.9',
						jobID:job.jobID
					},
					success:function(data) {
					
						if(data === null || data.status === 'bad') {
							alert('Error setting Job Status.');
						}
						else {
							
							console.log('> SUCCESS Job Status updated');
							
							navTo('/');
			
						}
					}
				}); // }
				
				break;
			// }
			
			case 'resubmitTask': // {
				
				// Set isCompleted {
				$.ajax( {
					url:'/mech/php/setSingle.php',
					error:function(xhr, txt, err) {
						alert('Error setting single\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
					},
					timeout:5000,
					data: {
						table:'Inspection',
						key:'isCompleted',
						value:0,
						index:'inspectionID',
						selector:task.inspectionID
					},
					success:function(data) {
					
						if(data === null || data.status === 'bad') {
							alert('Error setting isCompleted.');
						}
						else {
							
							console.log('> SUCCESS isCompleted updated');
						
						}
					}
				}); // }
				
				// Set isResubmitted {
				$.ajax( {
					url:'/mech/php/setSingle.php',
					error:function(xhr, txt, err) {
						alert('Error setting single\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
					},
					timeout:5000,
					data: {
						table:'Inspection',
						key:'isResubmitted',
						value:1,
						index:'inspectionID',
						selector:task.inspectionID
					},
					success:function(data) {
					
						if(data === null || data.status === 'bad') {
							alert('Error setting isResubmitted.');
						}
						else {
							
							console.log('> SUCCESS isResubmitted updated');
							
						}
					}
				}); // }
				
				// Set resubmissionNote {
				$.ajax( {
					url:'/mech/php/setSingle.php',
					error:function(xhr, txt, err) {
						alert('Error setting single\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
					},
					timeout:5000,
					data: {
						table:'Inspection',
						key:'resubmissionNote',
						value:'"' + $('#resubNote').val() + '"',
						index:'inspectionID',
						selector:task.inspectionID
					},
					success:function(data) {
					
						if(data === null || data.status === 'bad') {
							alert('Error setting resubmissionNote.');
						}
						else {
							
							console.log('> SUCCESS resubmissionNote updated');
							
							// Reset & close
							$('#modalBack').fadeOut();
							initialise();
			
						}
					}
				}); // }
				
				// Set Job Status {
				$.ajax( {
					url:'/mech/php/setJobStatus.php',
					error:function(xhr, txt, err) {
						alert('Error setting single\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
					},
					timeout:5000,
					data: {
						status:'1.1',
						jobID:job.jobID
					},
					success:function(data) {
					
						if(data === null || data.status === 'bad') {
							alert('Error setting Job Status.');
						}
						else {
							
							console.log('> SUCCESS Job Status updated');
			
						}
					}
				}); // }
								
				break;
			// }
			
			case 'updateClaimBrief': // {
				
				// Save Brief {
				$.post('/mech/php/setSingle.php',
					{
						table:'Job',
						key:'claimBrief',
						value:'"' + $('#resubNote').val() + '"',
						index:'jobID',
						selector:job.jobID
					},
					
					function(data) {
					
						if(data === null || data.status === 'bad') {
							
							alert('Error saving Brief.');
							
						}
						else {
							
							console.log('> SUCCESS Brief updated');
							initialise();
							$('#modalButtonNo').click();
							
						}
						
					}
					
				); // }
				
				break; // }
			
		} // }
		
	} // }
	
}); // }

// Dynamics }
// ==============

//======================
// { listeners‚

// { Home
$('#navJobs').click(function() {
	
	navTo('/home/');
	
});
// } Home

// { Job Info - Claim Brief
$('#jiblClaimBrief').click(function() {
	
	if($('#abApprove').attr('tag') !== 'complete') {
		
		// Build modal
		$('#modal').attr('tag', 'updateClaimBrief');
		$('#modalText1').html('Claim Brief');
		$('#modalText2').html('Enter instructions for the inspector');
		$('#modalBox').html('<textarea id="resubNote">' + job.claimBrief + '</textarea>');
		$('#modalButtonYes').html('Save');
		$('#modalButtonNo').html('Cancel');
		
		// Show it!
		$('#modalBack').fadeIn();
		
	}
		
});
// } Job Info - Claim Brief

// { Job Info - PDF
$('#jitPDF').on({
	
	click:function() {
		
		if(task.isScope === '1') {
			
			window.open('/mech/php/makeScopePDF.php', '_blank');
			
		}
		if(task.isCausation === '1') {
			
			window.open('/mech/php/makeCausationPDF.php', '_blank');
			
		}
		
	}
	
});
// } Job Info - PDF

// Action Bar - Grouping {
$('#abGrouping').click(function() {
	
	// Open
	if($(this).attr('state') === 'open') {
		
		$(this).html(
			'Group by...'
		);
		$(this).css('height', '25px');
		$(this).attr('state', 'closed');
		
	}
	
	// Closed
	else {
		
		$(this).html(
			'Close...' +
			'<a class="subDrop">Building</a>'
		);
		$(this).css('height', '55px');
		$(this).children().last().css('border-bottom-right-radius', '3px');
		$(this).children().last().css('border-bottom-left-radius', '3px');
		$(this).children().last().css('height', '28px');
		$(this).attr('state', 'open');
		
	}
	
});
// }

// { Job Info - Assign
$('#jibrAssign').click(function() {
	
	var boxText;
	
	// Build Inspector Picker {
	var select = '<select id="modalPicker">';
	for(var inspector in inspectors) { if(inspectors.hasOwnProperty(inspector)) {
		
		inspector = inspectors[inspector];
		select += '<option value="' + inspector.userID + '">' + inspector.name + '</option>';
		
	}}
	select += '</select>';
	// }
	
	// Assign {
	if($(this).attr('tag') === 'assign') {
		
		// Build Box Text {
		boxText = 'This claim requires:';
		if(job.wantsCausation === '1') {
			
			boxText += '<h3>+ Causation Report</h3>';
			
		}
		if(job.wantsScope === '1') {
			
			boxText += '<h3>+ Scope of Works</h3>';
		}
		boxText += 'Please select an Inspector to assign this task to, then click "Submit".';
		// }
		
		// Add to page
		$('#modalText1').html('Assign an Inspector');
		$('#modalText2').html('');
		$('#modalBox').html(boxText + select);
		$('#modalButtonNo').html('Cancel');
		$('#modalButtonYes').html('Save');
		$('#modal').attr('tag', 'assignTask');
		
	} // }
	
	// Reassign {
	else {
		
		// Build Box Text {
		boxText = 
			'Current inspector:' +
			'<h3>+ ' + task.assignedName + '</h3>' +
			'New inspector:'
		;
		// }
		
		$('#modalText1').html('Reassign to a new Inspector');
		$('#modalText2').html('');
		$('#modalBox').html(boxText + select);
		$('#modalButtonNo').html('Cancel');
		$('#modalButtonYes').html('Save');
		$('#modal').attr('tag', 'reassignTask');
		
	} // }
	
	$('#modalBack').fadeIn();
	
});
// } Job Info - Assign

// { Job Info - Resub
$('#jibrResub').on({
	
	click:function() {
		
		$('#modalText1').html('Resubmit for amendment');
		$('#modalText2').html('Enter reason for resubmission');
		$('#modalBox').html('<textarea id="resubNote"></textarea>');
		$('#modalButtonNo').html('Cancel');
		$('#modalButtonYes').html('Resubmit');
		$('#modal').attr('tag', 'resubmitTask');
		$('#modalBack').fadeIn();
		
	}
	
});
// } Job Info - Resub

// { Job Info - Approve
$('#jibrApprove').click(function() {
	
	// Incomplete
	if($(this).attr('tag') === 'incomplete') {
		
		$(this).effect('highlight', { color:'#F00' }, 500);
		
	}
	
	// Complete
	else {
		
		$('#modalText1').html('Approve Inspection');
		$('#modalText2').html('This Task has been flagged as complete. Click "Yes" to Approve.');
		$('#modal').attr('tag', 'approveTask');
		$('#modalBack').fadeIn();
		
	}
	
});
// } Job Info - Approve

// Action Bar - More {
$('#abMore').click(function() {
	
	// Open
	if($(this).attr('state') === 'open') {
		
		$(this).html(
			'More actions...'
		);
		$(this).animate({
			height:'25px'
		},100);
		//$(this).css('height', '25px');
		$(this).attr('state', 'closed');
		
	}
	
	// Closed
	else {
		
		if(job.status === '9.9') {
			
			$(this).html(
				'Close...' +
				'<a class="subDrop" id="exportPDF">Export as PDF</a>'
				//'<a class="subDrop">Help</a>'
			);
			$(this).animate({
				height:'55px'
			},100);
			//$(this).css('height', '55px');
			
		}
		else {
			
			$(this).html(
				'Close...' +
				'<a class="subDrop" id="resubmit">Resubmit Task</a>'
				//'<a class="subDrop">Help</a>'
			);
			$(this).animate({
				height:'55px'
			},100);
			//$(this).css('height', '85px');
			
		}
		
		$(this).children().last().css('border-bottom-right-radius', '3px');
		$(this).children().last().css('border-bottom-left-radius', '3px');
		$(this).children().last().css('height', '28px');
		$(this).attr('state', 'open');
		
	}
	
});
// }

// { Modal Back
$('#modalBack').on({
	
	click:function(e) {
		
		if($(e.target).is('#modalBack')) {
		
			$('#modalBack').fadeOut();
		
		}
		
	}
	
});
// } Modal Back

// } Listeners
// =====================

// =====================
// Hover listeners {

// Book Icon Parent {
$('.iconBookParent').hover(
	// In
	function() {
		
		// Icon
		$(this).children().attr('src', '/src/images/icons/bookBlack.png');
		
		// Open Popup
	},
	// Out
	function() {
		
		// Icon
		$(this).children().attr('src', '/src/images/icons/bookGrey.png');
		
		// Close Popup
	}
); // }

// Doc Icon Parent {
$('.iconDocParent').hover(
	// In
	function() {
		
		// Icon
		$(this).children().attr('src', '/src/images/icons/docBlack.png');
		
		// Open Popup
	},
	// Out
	function() {
		
		// Icon
		$(this).children().attr('src', '/src/images/icons/docGrey.png');
		
		// Close Popup
	}
); // }

// Hover listeners }
// =====================

//================
// Functions {

function drawPage() {
	
	// { Clear any existing data
	$('.mainBody').children('.buildingHead').detach();
	$('.mainBody').children('.buildingBox').detach();
	$('.mainBody').children('#splash').detach();
	// } Clear any existing data
	
	// Breadcrumb
	$('#navJob').html('/    ' + job.claimNumber);
	
	//console.log('Task ' + task.inspectionID);
	
	// { Task doesn't exist
	if(task === 'none') {
		
		$('#jiblCausationNotes').hide();
		$('#jibrAssign').html('Assign Inspector');
		$('#jibrAssign').attr('tag', 'assign');
		$('.mainBody').append('<div id="splash">No Task has been assigned yet...</div>');
		
	}
	// } Task doesn't exist
	
	// { Task exists
	else {
		
		console.log('Task Complete: ' + task.isCompleted);
		
		// Task in progress {
		if(task.isCompleted === '0') {
			
			// Task not yet accepted
			if(task.isAccepted === '0') {
				
				$('.mainBody').append('<div id="splash">Awaiting Inspector Confirmation...</div>');
				
			}
			else {
				
				$('.mainBody').append('<div id="splash">This task is currently in progress...</div>');
				
			}
			
			$('#jiblCausationNotes').hide();
			
			$('#jibrAssign').html('Re-assign Inspector');
			$('#jibrAssign').attr('tag', 'reassign');
			$('#jibrAssign').show();
			
		}
		// }
		
		// Task Complete {
		else {
			
			$('#jitPDF').show();
			
			// Causation Notes {
			if(task.causationNotes === '') {
				
				$('#jiblCausationNotes').attr('title', 'No causation notes entered.');
				
			}
			else {
				
				$('#jiblCausationNotes').attr('title', task.causationNotes);
				
			}
			$('#jiblCausationNotes').tooltip({track:true});
			// }
			
			// Action Bar {
			// Already approved
			if(job.status === '9.9') {
				
				$('#jibrAssign').hide();
				$('#jibrResub').hide();
				$('#jibrApprove').hide();
				
			}
			else {
				
				$('#jibrAssign').html('Re-Assign Inspector');
				$('#jibrAssign').attr('tag', 'reassign');
				$('#jibrAssign').show();
				
				$('#jibrResub').html('Resubmit Job');
				$('#jibrResub').show();
				
				$('#jibrApprove').html('Approve Report');
				$('#jibrApprove').attr('tag', 'complete');
				$('#jibrApprove').show();
			
			}
			// }
			
			// Buildings {
			if(buildings !== 'none') {
			
				for(var building in buildings) { if(buildings.hasOwnProperty(building)) {
					
					id       = building;
					building = buildings[building];
					
					// Build age
					if(building.age === '0') {
						
						building.age = building.period;
						
					}
					
					// Building image
					if(building.image === '') {
					
						building.image = '/src/images/noImage.png';
						
					}
					
					$('.mainBody').append(
						
						'<div class="buildingHead">' +
						
							'<div class="bhName">' + building.name + '</div>' +
							'<div class="bhIcons">' + 
								
								'<img class="bhiImage iconCamera" src="/src/images/icons/cameraGrey.png"/>' +
								'<img class="bhiNotes iconDoc" src="/src/images/icons/docGrey.png"/>' +
								'<a></a>' +
								
							'</div>' +
							
						'</div>' +
						'<div class="buildingBox" tag="' + id + '">'
						
					);
					
				}}
				
			}
			else {
				
				$('.mainBody').append('<div id="splash">This Task has no Buildings to display...</div>');
				
			} // }
		
			// Locations {
			if(locations !== 'none') {
				
				for(var location in locations) { if(locations.hasOwnProperty(location)) {
					
					id       = location;
					location = locations[location];
					
					$('.buildingBox[tag="' + location.buildingID + '"]').append(
						
						'<div class="locationBox" tag="' + id + '">' +
						
							'<div class="locationHead" tag="' + id + '">' +
							
								'<div class="locationName">'       + location.name   + '</div>' +
								'<div class="locationDimensions">' + location.length + ' x ' + location.width + ' x ' + location.height + 'mm</div>' +
								'<div class="locationNotes">'      + location.notes  + '</div>' +
								
							'</div>' +
							
						'</div>'
					);
					
				}}
				
			} // }
		
			// Elements {
			if(elements !== 'none') {
				
				for(var element in elements) { if(elements.hasOwnProperty(element)) {
					
					id       = element;
					element = elements[element];
									
					// Create Icons {
					var icons = '';
					
					// Notes
					if(element.notes !== '') {
						
						icons += '<img class="elInfo iconDoc" src="/src/images/icons/docGrey.png"/>';
						
					}
					
					// }
					var paint = ' - ';
					if(element.paintQty !== '0') {

						paint = element.paintQty + ' ' + element.units;
						
					}
					
					// { Build Rectification types
					
					var tmpRects = element.rectification.split(', ');
					var rectifications = '';
					
					// Per rect
					for(var recType in tmpRects) { if(tmpRects.hasOwnProperty(recType)) {
						
						recType = tmpRects[recType];
						rectifications += '<div class="erRectificationType rect' + recType + '">' + recType + '</div>';
						
					}}
					
					// } Build Rectification types
					
					$('.locationBox[tag="' + element.locationID + '"]').append(
						
						'<div class="element" tag="' + id + '">' +
						
							'<div class="elementLeft">' +
							
								'<div class="elCategory">'  + element.category  + ':</div>' +
								'<div class="elName">'      + element.name      + '</div>'  +
								'<div class="elAttribute">' + element.attribute + '</div>'  +
								'<div class="elIcons">'     + icons             + '</div>'  +
								
							'</div>' +
							
							'<div class="elementRight">' +
								
								'<div class="erRectification">' + rectifications                               + '</div>' +
								'<div class="erQty">'           + element.qty + ' ' + element.units            + '</div>' +
								'<div class="erPaint">'         + paint                                        + '</div>' +
								'<div class="erCondition">'     + element.cond                                 + '</div>' +
								'<div class="erCause">'         + element.causeCategory + ': ' + element.cause + '</div>' +
								
							'</div>' +
							
						'</div>'
						
					);
					
				}}
				
				// { Add Qtipts to rectifications
				$('.rectPP').attr('title', 'Prepare & Paint');
				$('.rectPP').tooltip({track:true});
				
				$('.rectRI').attr('title', 'Reinstall');
				$('.rectRI').tooltip({track:true});
				
				$('.rectRM').attr('title', 'Remove');
				$('.rectRM').tooltip({track:true});
				
				$('.rectRP').attr('title', 'Repair');
				$('.rectRP').tooltip({track:true});
				
				$('.rectSI').attr('title', 'Supply & Install');
				$('.rectSI').tooltip({track:true});
				
				$('.rectCL').attr('title', 'Cleanup');
				$('.rectCL').tooltip({track:true});
				// } Add Qtipts to rectifications
				
			} // }
		
			// Headings {
			
			// Building Box {
			$('.buildingHead').next().html(function() {
				
				// If Locations not present
				if($(this).html() === '') {
					
					$(this).prepend(
						'<div class="noneBar">' +
							'No Locations in this Building...' +
						'</div>'
					);
					
				}
			
			}); // }
			
			// Location Box {
			$('.locationHead').html(function() {
				
				// If Elements not present
				if($('.locationHead').siblings().size() === 0) {
					
					$('<div class="noneBar">' +
							'No Elements in this Location...' +
						'</div>'
					).insertAfter(this);
					
				}
				else {
					
					$('<div class="elementHead">' +
						
							'<div class="elementLeft">' +
								'<div class="elCategory">Element</div>' +
							'</div>' +
							'<div class="elementRight">' +
								
								'<div class="erRectification">Rectification</div>' +
								'<div class="erQty">Qty</div>' +
								'<div class="erPaint">Paint</div>' +
								'<div class="erCondition">Condition</div>' +
								'<div class="erCause">Cause</div>' +

							'</div>' +
							
						'</div>'
						
					).insertAfter(this);
					
				}
				
			}); // }
		
			// }
			
			// { Building Icons
			$('.bhiImage').each(function() { 
				
				$(this).tooltip({
					
					track:true,
					showURL:false,
					extraClass:'popupPic',
					bodyHandler:function() {
						
						return $('<img/>').attr('src', '../../src/' + buildings[$(this).parent().parent().next().attr('tag')].image.slice(5)).addClass('popupImage');
						
					}
					
				});
//				$(this).qtip($.extend({}, qtipSettings, {
//					
//					content:'<img class="popupImage" src="../../src/' + buildings[$(this).parent().parent().next().attr('tag')].image.slice(5) + '"/>'
//					
//				}));
			
			});
			
			$('.bhiNotes').each(function() { 
				
				$(this).attr('title', buildings[$(this).parent().parent().next().attr('tag')].notes);
				$(this).tooltip({track:true, showURL:false});
//				$(this).qtip($.extend({}, qtipSettings, {
//					
//					content:buildings[$(this).parent().parent().next().attr('tag')].notes
//					
//				}));
			
			});
			// } Building Icons
			
			// Element Notes {
			$('.elInfo').each(function() { 
				
				$(this).attr('title', elements[$(this).parent().parent().parent().attr('tag')].notes);
				$(this).tooltip({track:true, showURL:false});
//				$(this).qtip($.extend({}, qtipSettings, {
//					
//					content:elements[$(this).parent().parent().parent().attr('tag')].notes
//					
//				}));
			
			}); // }
			
		} // }
	
	}
	// } Task Exists
	
	// { Job Info
	
	// { Add images
	if(elements !== 'none') {
		
		getImages();
		
	}
	// } Add images
	
	// Build Job Info
	$('#jitAddress').html(job.address1 + ', ' + job.address2);
	$('#jiClaimant').html(job.claimant);
	$('#jinPhoneMobile').html(job.phoneMobile);
	$('#jinPhoneLandline').html(job.phoneLandline);
	var briefText;
	if(job.claimBrief === '') {
		
		briefText = 'Click to add Claim Brief';
		
	}
	else {
		
		briefText = job.claimBrief;
		
	}
	
	$('#jiblClaimBrief').attr('title', briefText);
	$('#jiblClaimBrief').tooltip({track:true});
//	$('#jiblClaimBrief').qtip($.extend({}, qtipSettings, {
//		
//		content:briefText
//		
//	}));
	// } Job Info

}

function getImages() {
	
	// Get Element Images {
	if(elements !== 'none') {
		
		// Build elementIDs {
		var elementIDs = [];
		for(var e in elements) { if(elements.hasOwnProperty(e)) {
			
			elementIDs.push(e);
			
		}}
		// }
	
		$.ajax( {
			url:'/mech/php/getDrilldownImages.php',
			data: {
				jobID      : job.jobID,
				elementIDs : elementIDs
			},
			error:function(xhr, txt, err) {
				alert('Error getting data\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
			},
			timeout:99999,
			success:function(data) {
			
				if(data === null || data.status === 'bad') {
					alert('Error getting Images.');
				}
				else {
					
					// Per Element Image
					for(var eI in data.elements) { if(data.elements.hasOwnProperty(eI)) {
						
						if(data.elements[eI] !== 'none') {
							
							id       = eI;
							eI = data.elements[eI];
							
							elements[id].imageThumb = eI.imageThumb;
							elements[id].imageFull  = eI.imageFull;
							
							//$('.mainBody').append(eI.imageThumb + '<br/><br/><br/>');
							
							// Insert Icon
							$('.element[tag="' + id +'"]').children().first().children().last().append('<img tag="' + id + '" class="elImage iconCamera" src="/src/images/icons/cameraGrey.png"/>');
							
							// Build Modal
							
							$('.mainBody').append(
								
								'<div id="modal' + id + '">' +
									'<img class="modalImage" src="' + eI.imageFull.slice(5) + '"/>' +
								'</div>'
								
							);
							$('.modalImage').hide();
						}
						
					}}
					
					// Element Photos Qtip {
					$('.elImage').each(function() { 
						
						$(this).tooltip({
							
							track:true,
							showURL:false,
							extraClass:'popupPic',
							top:15,
							left:15,
							bodyHandler:function() {
								
								return $('<img />').attr('src', elements[$(this).parent().parent().parent().attr('tag')].imageThumb.slice(5)).addClass('popupImage');
								
							}
							
						});
//						$(this).qtip($.extend({}, qtipSettings, {
//							
//							content:'<img class="popupImage" src="' + elements[$(this).parent().parent().parent().attr('tag')].imageThumb.slice(5) + '"/>'
//							
//						}));
					
					}); // }
		
					console.log('> SUCCESS Get Drilldown Images');
					
				
				}
			}
		});
	}
	// }
}

// Functions }
// ===============