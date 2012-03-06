// home/index.js

// ===============
// Variables {

var jobs;
var insurers;
var coordinators;

// Variables }
// ===============

// ==================
// Initialisers {

// { Page finished loading
$(document).ready(function() {
	
	initialiseAll();
	
	initialise();
	
});
// } Page finished loading

function initialise() {
	
	// Get Jobs
	getJobs(true);
	
	// Get Managers
	getCoordinators();
	
	// Get Insurers
	getInsurers();
	
	// Get Inspectors
	getInspectors();
	
	// { Preload
	preload(
		'<img src="/src/images/avatars/defaultGreen.png" />' +
		'<img src="/src/images/icons/homeGreen.png" />'      +
		'<img src="/src/images/icons/questionGreen.png" />'  +
		'<img src="/src/images/icons/gearsGreen.png" />'     +
		'<img src="/src/images/icons/plusGreen.png" />'      +
		'<img src="/src/images/icons/folderGreen.png" />'    +
		'<img src="/src/images/icons/triDownWhite.png" />'   +
		'<img src="/src/images/icons/triRightBlack.png" />'  +
		'<img src="/src/images/icons/triRightWhite.png" />'
	);
	// } Preload
	
	// { Attach masks
	$('#njPostCode').mask('9999', { placeholder:' ' });
	$('#njMobile').mask('9999 999 999', { placeholder:' ' });
	$('#njLandline').mask('(99) 9999 9999', { placeholder:' ' });
	// } Attach masks
	
}

// Initialisers }
// ==================

// ===============
// Listeners {

// { New Job
$('#newJob').click(function() {
	
	// { Clear pickers
	$('#njInsurer').html('<option value="" selected="selected">Please select an Insurer</option>');
	$('#njOwner').html('');
	$('#njInspector').html('<option value="" selected="selected">Select an Inspector</option>');
	// } Clear pickers
	
	// { Build insurer picker
	
	// { Make selections
	delete insurers.length;
	for(var insurer in insurers) { if(insurers.hasOwnProperty(insurer)) {
		
		insurer = insurers[insurer];
		$('#njInsurer').append('<option value="' + insurer.companyID + '">' + insurer.name + '</option>');
		
	}}
	// } Make selections
	
	// { Default insurer if webUser is insurance company
	if(session.isInsurer === '1') {
		
		$('#njInsurer').children('[val="' + session.companyID + '"]').attr('selected', 'selected');
		$('#njInsurer').attr('tag', session.companyID);
		
	}
	// } Default insurer if webUser is insurance company
	
	// } Build insurer picker
	
	// { Build owner picker
	
	// { Make selections
	delete coordinators.length;
	for(var coordinator in coordinators) { if(coordinators.hasOwnProperty(coordinator)) {
		
		coordinator = coordinators[coordinator];
		$('#njOwner').append('<option value="' + coordinator.userID + '">' + coordinator.name + '</option>');
		
	}}
	// } Make selections
	
	// { Default owner is me
	$('#njOwner').children('[value="' + session.userID + '"]').attr('selected', 'selected');
	$('#njOwner').attr('tag', session.userID);
	// } Default owner is me
	
	// } Build owner picker
	
	// { Build inspector picker
	delete inspectors.length;
	for(var inspector in inspectors) { if(inspectors.hasOwnProperty(inspector)) {
		
		inspector = inspectors[inspector];
		$('#njInspector').append('<option value="' + inspector.userID + '">' + inspector.name + '</option>');
		
	}}	
	// } Build inspector picker
	
	console.log('Creating new Job');
	$('#njFrame1').show();
	$('#njFrame2').hide();
	$('#njBack').fadeIn(250).removeClass('hidden');
	
});
// } New Job

// { New Job - Overlay
$('#njBack').on({
	
	click:function(e) {
		
		if($(e.target).is('#njBack')) {
		
			$('#njNavCancel').click();
		
		}
	}

});
// } New Job - Overlay

// { New Job - Cancel
$('#njNavCancel').on({
	
	click:function(e) {
	//	if(
	//		$('#njFirstName').attr('value')   !== '' ||
	//		$('#njLastName').attr('value')    !== '' ||
	//		$('#njAddress').attr('value')     !== '' ||
	//		$('#njSuburb').attr('value')      !== '' ||
	//		$('#njPostCode').attr('value')    !== '' ||
	//		$('#njMobile').attr('value')      !== '' ||
	//		$('#njLandLine').attr('value')    !== '' ||
	//		$('#njEmail').attr('value')       !== '' ||
	//		$('#njInspector').attr('value')   !== '' ||
	//		$('#njClaimNumber').attr('value') !== ''
	//		) {
	//		alert('Warning!\nAny changes you made will be lost.');
	//	}
		
		console.log('Cancelled New Job');
		clearNewJob();
		$('#njBack').fadeOut(250);
		e.stopPropagation();
		
	}
	
});
// } New Job - Cancel

// { New Job - Next
$('#njNavNext').click(function() {
	
	var ready = true;
	
	// Check for required fields
	if($('#njFirstName').attr('value') === '') {
		$('#njFirstName').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njFirstName').prev().css('color', '#000');
	}
	
	if($('#njLastName').attr('value') === '') {
		$('#njLastName').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njLastName').prev().css('color', '#000');
	}
	
	if($('#njAddress').attr('value') === '') {
		$('#njAddress').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njAddress').prev().css('color', '#000');
	}
	
	if($('#njSuburb').attr('value') === '') {
		$('#njSuburb').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njSuburb').prev().css('color', '#000');
	}
	
	if($('#njPostCode').attr('value') === '' || $('#njPostCode').attr('value').length !== 4) {
		$('#njPostCode').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njPostCode').prev().css('color', '#000');
	}
	
	if($('#njMobile ').attr('value') === '' && $('#njLandline ').attr('value') === '') {
		$('#njMobile, #njLandline').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njMobile').prev().css('color', '#000');
		$('#njLandline').prev().css('color', '#000');
	}
	
	if($('#njInsurer').attr('tag') === '') {
		$('#njInsurer').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njInsurer').prev().css('color', '#000');
	}
	
	if($('#njClaimNumber').attr('value') === '') {
		$('#njClaimNumber').prev().css('color', '#990000');
		ready = false;
	}
	else {
		$('#njClaimNumber').prev().css('color', '#000');
	}   
	
	if(ready) {
		console.log('Next panel');
		// Proceed
		$('#njFrame1').flip({
			direction:'rl',
			onEnd:function() {
				
				$('#njFrame2').css('background-color', '#FFF');
				$('#njFrame2').show();
				$('#njFrame1').hide();
				
			}
		});
	}
	else {
		console.log('Missing some fields');
	}
});
// } New Job - Next

// { New Job - Back
$('#njNavBack').click(function() {
	
	$('#njFrame2').flip({
		
		direction:'lr',
		onEnd:function() {
			
			$('#njFrame1').css('background-color', '#FFF');
			$('#njFrame1').show();
			$('#njFrame2').hide();
			
		}
		
	});
	
});
// } New Job - Back

// { New Job - Save
$('#njNavSave').click(function() {
	
	var ready = true;
	
	// Validate
	if($('#njOwner').attr('tag') === '') {
	
		$('#njOwner').prev().css('color', '#990000');
		ready = false;
	
	}
	else {
	
		$('#njOwner').prev().css('color', '#000');
	
	}
	
	if(ready) {
	
		// Add Job to DB
		addNewJob();
					
	}
	else {
	
		console.log('Missing some fields');
	
	}
   
});
// } New Job - Save

// { New Job - Insurer
$('#njInsurer').on({
	
	change:function() {
		
		$(this).attr('tag', $(this).children('[selected="selected"]').val());
		
	}
	
});
// } New Job - Insurer picker

// { New Job - Owner
$('#njOwner').on({
	
	change:function() {
		
		$(this).attr('tag', $(this).children('[selected="selected"]').val());
		
	}
	
});
// } New Job - Owner picker

// { New Job - Inspector
$('#njInspector').on({
	
	change:function() {
		
		$(this).attr('tag', $(this).children('[selected="selected"]').val());
		
	}
	
});
// } New Job - Inspector picker

// { Job
$('.jobsBox').on({
	
	click:function() {
		
		// Ignore emptyJob boxes
		if(!$(this).hasClass('emptyJob')) {
			
			console.log('Job clicked');
			$.post('/mech/php/setSessionData.php',
				{
					
					key   :'selectedJob',
					value :$(this).attr('tag')
					
				},
				function(data) {
					
					// Good
					if(data.status === 'good') {
						
						console.log('Selected Job Set.');
						navTo('/home/job/');
						
					}
					// Bad
					else {
						
						console.log('Hmm...');
						
					}
				}
				
			);
		
		}
	}

}, '.job');
// } Job

// listeners }
// ===============

// ===============
// Functions {

// Load in Jobs
function getJobs(populate) {
	
	$.post('/mech/php/getJobs.php',
		
		function(data) {
			
			if(data === null) {
				
				alert('Error getting Jobs: Bad JSON decode');
				
			}
			else {
				
				jobs = data;
				console.log('> SUCCESS Get Jobs');
				if(populate) {
					
					populateJobList();
					
				}
				
			}
			
		}
		
	);
	
}

// Load in Insurers
function getInsurers() {

	$.post('/mech/php/getInsurers.php',
		
		function(data) {
			
			insurers = data;
			console.log('> SUCCESS Get Insurers');
				
		}
		
	);
	
}

// Load Coordinators
function getCoordinators() {

	$.post('/mech/php/getCoordinators.php',
		
		function(data) {
		
			coordinators = data;
			console.log('> SUCCESS Get Coordinators');

		}
		
	);
	
}

// Clear all New Job fields
function clearNewJob() {
	
	$('#njBrief').attr('value', '');
	$('.njField').children('input').attr('value', '');
	$('.njField').children('label').css('color', '#000');
	$('.njFieldCheck').children('img').hide();
	$('.njFieldCheck').attr('tag', '0');
	$('.njFieldPicker').find('input').attr('tag', '');
	$('.njFieldPicker').find('input').attr('ext', '');
	
}

// Fill page with Jobs
function populateJobList() {
	
	// Clear Job boxes
	$('#boxUnassigned').children().last().html('');
	$('#boxAssigned').children().last().html(  '');
	$('#boxAccepted').children().last().html(  '');
	$('#boxReturned').children().last().html(  '');
	$('#boxCompleted').children().last().html( '');
	
	// { Fill Jobs boxes
	for(var job in jobs) { if(jobs.hasOwnProperty(job)) {
	
		insertJob(jobs[job]);
	
	}}
	// } Fill Jobs Boxes
	
	// { Draw box footer
	$('.jobsBox').each(function() {
		
		var jobCount = $(this).children().last().children().size();
		var jobString = 'jobs';
		var jobCountMessage;
		// { If no jobs
		if(jobCount === 0) {
			
			jobCount = 'No';
			
		}
		// } If no jobs
		// { Else
		else {
			
			// { Set footer text
			if(jobCount === 1) {
				
				jobString = 'job';
				
			}
			// } Set footer text
			
		}
		// } Else
		
		switch($(this).attr('id')) {
			
			case 'boxUnassigned':
				
				jobCountMessage = jobCount + ' new ' + jobString;
				console.log(jobCount + ' ' + jobString);
				break;
				
			case 'boxAssigned':
				
				jobCountMessage = jobCount + ' ' + jobString + ' awaiting inspector confirmation';
				
				break;
				
			case 'boxAccepted':
				
				jobCountMessage = jobCount + ' ' + jobString + ' with inspector';
				
				break;
				
			case 'boxReturned':
				
				jobCountMessage = jobCount + ' ' + jobString + ' waiting for approval';
				
				break;
				
			case 'boxCompleted':
				
				jobCountMessage = jobCount + ' ' + jobString + ' recently completed';
				
				break;
			
		}
			
		$(this).children().last().append('<div class="job emptyJob">' + jobCountMessage + '</div>');
		
	});
	// { Draw box footer
	
}

// Insert single Job
function insertJob(job) {
	
	var jobStr =
		'<a class="job" id="job' + job.jobID + '" tag="' + job.jobID + '">' +
			'<div class="jobIcon">'                       + '</div>'     +
			'<div class="jobNumber">'   + job.claimNumber + '</div>'     +
			'<div class="jobAddress">'  + job.address     + '</div>'     +
			'<div class="jobClaimant">' + job.claimant    + '</div>'     +
			'<div class="jobInsurer">'  + job.insurer     + '</div>'     +
			'<div class="jobAge">'      + job.age         + ' old</div>' +
		'</a>';
		
	var iconWarn = '<img class="warn" src="/src/images/icons/warnYellow.png"/>';
	var iconProb = '<img class="prob" src="/src/images/icons/probRed.png"/>';
	
	// Sort
	switch(job.status) {
		
		// Unassaigned {
		case '0.1':
			
			$('#boxUnassigned').children().last().append(jobStr);
			break;
		// }
		
		// Unassigned (Declined) {
		case '0.2':
			
			$('#boxUnassigned').children().last().append(jobStr);
			$('#boxUnassigned').children().last().children().last().children().first().attr('title', 'This Task was declined');
			$('#boxUnassigned').children().last().children().last().children().first().html(iconProb).tooltip({track:true});
			break;
		// }
		
		// Assigned {
		case '1.0':
			
			$('#boxAssigned').children().last().append(jobStr);
			break;
		// }
		
		// Assigned (Resubmitted) {
		case '1.1':
			
			$('#boxAssigned').children().last().append(jobStr);
			$('#boxAssigned').children().last().children().last().children().first().attr('title', 'This Task has been resubmitted for amendment');
			$('#boxAssigned').children().last().children().last().children().first().html(iconWarn).tooltip({track:true});
			break;
		// }
		
		// In Progress {
		case '2.0':
			
			$('#boxAccepted').children().last().append(jobStr);
			break;
		// }
		
		// In Progress (Resubmitted) {
		case '2.1':
			
			$('#boxAccepted').children().last().append(jobStr);
			$('#boxAccepted').children().last().children().last().children().first().attr('title', 'This Task has been resubmitted for amendment');
			$('#boxAccepted').children().last().children().last().children().first().html(iconWarn).tooltip({track:true});
			break;
		// }
		
		// Completed {
		case '4.0':
			
			$('#boxReturned').children().last().append(jobStr);
			break;
		// }
		
		// Completed (Resubmission) {
		case '4.0':
			
			$('#boxReturned').children().last().append(jobStr);
			$('#boxReturned').children().last().children().last().children().first().attr('title', 'This Task has been resubmitted for amendment');
			$('#boxReturned').children().last().children().last().children().first().html(iconWarn).tooltip({track:true});
			break;
		// }
		
		// Approved {
		case '9.9':
			
			$('#boxCompleted').children().last().append(jobStr);
			break;
		// }
			
	}
	
}

function addNewJob() {
	
	console.log('Start addNewJob');
	
	$.post('/mech/php/addJob.php',
	
		{
			firstName     :$('#njFirstName').attr('value'),
			lastName      :$('#njLastName').attr('value'),
			locAddress    :$('#njAddress').attr('value'),
			locSuburb     :$('#njSuburb').attr('value'),
			locPostCode   :$('#njPostCode').attr('value'),
			phoneMobile   :$('#njMobile').attr('value'),
			phoneLandline :$('#njLandline').attr('value'),
			insurerID     :$('#njInsurer').attr('tag'),
			claimNumber   :$('#njClaimNumber').attr('value'),
			claimBrief    :$('#njBrief').attr('value'),
			causation     :$('#njCausation').prop('checked'),
			scope         :$('#njScope').prop('checked'),
			costing       :$('#njCosting').prop('checked'),
			ownerID       :$('#njOwner').attr('tag')
		},
		
		function(data) {
			
			if(data.status === 'good') {
				
				console.log('New Job Created!');
				
				// If an Inspector has been set
				if($('#njInspector').attr('tag') !== '') {
					
					// Create Inspection
					addNewInspection(data.jobID, $('#njInspector').attr('tag'), $('#njCausation').attr('tag'), $('#njScope').attr('tag'));
					initialise();
					
				}
				else {
					
					initialise();
					
				}
				
				clearNewJob();
				$('#njBack').fadeOut(250);
				
			}
			else {
				
				console.log('Job failure returned');
				
			}
			
		}
		
	);
	
}

// Functions }
// ===============