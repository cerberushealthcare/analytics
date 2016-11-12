

<html>
	<head>
		<script type="text/javascript" src="../../../flight/web/js/jquery-1.7.1.min.js"></script>
	
		<script type="text/javascript">
			var files = [	'_SqlRec_ReservedOracleWords_ConvertsWord.php',
								'apiLogin_ReturnsNoErrors.php',
								'warren/loginSession_fetchUserWithLogging_ReadCorrectData.php',
								'loginSession_FetchUser_isValidResult.php',
								'loginSession_fetchUserWithLogging_isValidResult.php',
								'loginSession_FetchUser_isValidResult.php',
								'AssocArrayClass_QueryReturnsCorrectResult.php'];
				
			var tests = 0;
			var testsPassed = 0;
			var testsFailed = 0;
				
			ajaxCalls = function() {
				for (var i = 0, len = files.length; i < len; i++) {
					tests++;
					var urlToLoad = files[i];
					$.ajax({
						url: urlToLoad,
						method: 'GET',
						success: function(html) {
							$('#results').append(html);
							$('#results').append('<br>------------<br><br>');
							testsPassed++;
						  },
						 error: function (xhr, e) {
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