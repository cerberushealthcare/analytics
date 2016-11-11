<?php
	set_include_path('../');
?>

<html>
	<head>
		<script type="text/javascript" src="../../flight/web/js/jquery-1.7.1.min.js"></script>
	
		<script type="text/javascript">
			var files = [	'unit/_SqlRec_ReservedOracleWords_ConvertsWord.php',
								'unit/apiLogin_ReturnsNoErrors.php',
								'unit/loginSession_FetchUser_isValidResult.php',
								'unit/loginSession_fetchUserWithLogging_isValidResult.php',
								'unit/warren.loginSession_fetchUserWithLogging_ReadCorrectData.php'];
				
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
							$('#results').append('<b>' + files[i] + '</b>:<br>');
							$('#results').append(html);
							$('#results').append('<br>------------<br><br>');
							testsPassed++;
						  },
						 error: function (xhr, e) {
							$('#results').append('<b>' + files[i] + '</b>:<br>');
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