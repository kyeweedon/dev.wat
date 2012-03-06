

	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
// Page finished loading
$(document).ready(function() {
	
	console.log('> SUCCESS Page rendering');
	
});

// Login button click (or enter)
$('go').click(function() {
	
	doLogin();
	
});

// Authenticate
var doLogin = function() {
	
	var un = $('#userName').attr('value');
	var pw = $('#passWord').attr('value');
	var ok = true;
	
	// Validate input
	if(un === '') {
		
		console.log('Missing username');
		ok = false;
		
	}
	
	if(pw === '') {
		
		console.log('Missing password');
		ok = false;
		
	}
	
	// Send attempt to authenticator
	if(ok) {
		console.log('Authenticating user...');
		$.post('/mech/php/login.php',
			{
				userName:un,
				passWord:pw
			},
			function(data) {
				
				if(data === 'good') {
					
					console.log('> SUCCESS Authentication');
					window.location.href = '/home/';
						
				}
				else {
					
					console.log('> FAIL Authentication');
					// Shake it!
					$('#login').effect('shake', { times:2, distance:15 }, 70);
					
				}
				
			}
			
		);
		
	}
	else {
		
		// Shake it!
		$('#login').effect('shake', { times:2, distance:15 }, 70);
		
	}
	
};