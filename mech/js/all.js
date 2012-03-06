// mech/js/all.js

console.log('Loading page...');

// Define globals {

var session;
var inspectors;
var qtipSettigs;

// }

// Per page load {
function initialiseAll() {
	
	console.log('Initialise All');
	
	// AJAX settings {
	$.ajaxSetup( {
		
		type:'POST',
		dataType:'json',
		cache:false,
		timeout:9999,
		error:function(xhr, txt, err) {
			
			alert('Error getting Session data\n\nReady State: ' + xhr.readyState + '\nStatus: '+ xhr.status + '\nResponse Text: ' + xhr.responseText + '\nError: ' + txt + '(' + err + ')');
			
		}
		
	}); // }
	
	// QTip Settings {
	qtipSettings = {
		
		position: {
			my:'top left',
			target:'mouse',
			viewport:$(window),
			adjust: {
				x:20,
				y:20
			}
		},
		hide: {
			fixed:true
		},
		style:'ui-tooltip-dark ui-tooltip-rounded'
		
	}; // }
	
	// Get session data {
	$.post('/mech/php/getSessionData.php', function(data) {
			
		if(data === null || data.status === 'bad') {
			
			alert('Error getting Session data.');
			
		}
		else {
			
			// Load into local holder
			session = data.data;
			buildPageAll();
			
		}
		
	}); // }
	
} // }

// ===============
// Listeners {

// Future features {
$('body').on({
	
	click:function() {
		
		alert('Please contact "Metric" for details on this future option.');
		
	}
	
}, '.future'); // }

// Listeners }
// ===============

// ======================
// Global Functions {

// Filler for all pages
function buildPageAll() {
	
	// Setup future options
	$('.future').attr('disabled', 'disabled');
	
}

// Navigation controller
function navTo(xLoc) {
	
	window.location.href = xLoc;
	
}

// Capitalise first letter of a string
function ucfirst(str) {
	
    str += '';
    var fixed;
    
    fixed = str.charAt(0).toUpperCase();
    return fixed + str.substr(1);
    
}

// Preloader
function preload(str) {
	
   $('#preload').html(str);
   console.log('> SUCCESS Preload Elements');
   
}

// Update Job Status
function setJobStatus(xJobID, xStatus) {
	
	$.post('/mech/php/setJobStatus.php',
		{
			jobID  :xJobID,
			status :xStatus
		},
		function(data) {
			
			console.log('Job ' + xJobID + ' Status = ' + xStatus);
			
		}
		
	);
	
}

// Load inspectors
function getInspectors() {

	$.post('/mech/php/getInspectors.php',
		
		function(data) {

			inspectors = data;
			console.log('> SUCCESS Get Inspectors');
				
		}
		
	);
	
}

// Create New Inspection
function addNewInspection(xJobID, xInspectorID, xIsCausation, xIsScope) {
	
	console.log(xIsCausation + ' ' + xIsScope);
	
	$.post('/mech/php/addInspection.php',
	
		{
			jobID             :xJobID,
			isCausation       :xIsCausation,
			isScope           :xIsScope,
			inspectorID       :xInspectorID
		},
		
		function(data) {
		
			if(data.status === 'good') {
				
				console.log('New Inspection Created!');
				
				// Update status to 1.0
				setJobStatus(xJobID, '1.0');
				
			}
			else {
				
				console.log('Failed to create Inspection');
			
			}
		
		}
	
	);
   
}

function doQuery(xSQL) {
	
	$.post('/mech/php/doQuery.php',
		
		{
			query:xSQL
		},
		
		function(data) {
			
			if(data.status === 'good') {
				
				if(typeof data.result !== 'undefined') {
					
					return data.result;
					
				}
				else {
					
					return true;
					
				}
				
			}
			else {
				
				return false;
				
			}
			
		}
		
	);
	
}

// Global Functions }
// ======================