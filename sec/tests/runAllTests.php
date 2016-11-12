<?php
	set_include_path('../');
	ini_set('display_errors', '0');
?>

<html>
	<head>
		<script type="text/javascript" src="../../flight/web/js/jquery-1.7.1.min.js"></script>
	
		<script type="text/javascript">
			var files = [	'unit/_SqlRec_ReservedOracleWords_ConvertsWord.php',
								//'unit/apiLogin_ReturnsNoErrors.php',
								//'unit/loginSession_FetchUser_isValidResult.php',
								//'unit/loginSession_fetchUserWithLogging_isValidResult.php',
								//'unit/warren.loginSession_fetchUserWithLogging_ReadCorrectData.php',
								'../tester.php?t=1',
								'../tester.php?t=2',
								'../tester.php?t=3',
								'../tester.php?t=4',
								'../tester.php?t=5',
								'../tester.php?t=6',
								'../tester.php?t=7',
								'../tester.php?t=8',
								'../tester.php?t=9',
								'../tester.php?t=10',
								'../tester.php?t=11',
								'../tester.php?t=12',
								'../tester.php?t=13',
								'../tester.php?t=14',
								'../tester.php?t=15',
								'../tester.php?t=16',
								'../tester.php?t=17',
								'../tester.php?t=18',
								'../tester.php?t=20',
								'../tester.php?t=21',
								'../tester.php?t=22',
								'../tester.php?t=23',
								'../tester.php?t=24',
								];
				
			var tests = 0;
			var testsPassed = 0;
			var testsFailed = 0;
				
			ajaxCalls = function() {
				for (var i = 0, len = files.length; i < len; i++) {
					tests++;
					$.ajax({
						url: files[i],
						method: 'GET',
						success: function(html) {
							$('#results').append('<b>' + this.url + '</b>:<br>');
							$('#results').append(html);
							$('#results').append('<br>------------<br><br>');
							testsPassed++;
						  },
						 error: function (xhr, e) {
							$('#results').append('<b>' + this.url + '</b>:<br>');
							$('#results').append('ERROR: ' + xhr.status + ': ' + xhr.responseText);
							$('#results').append('<br>------------<br><br>');
							testsFailed++;
						  }
					});
				}
			}
			
			$.when(ajaxCalls).done(function(a){
				$('#results').prepend('Total tests: ' + tests + ', Passed: ' + testsPassed + ', Failed: ' + testsFailed);
			});
		</script>
	</head>
	<body onload="ajaxCalls();">
		<div id="results"></div>
	</body>
</html>