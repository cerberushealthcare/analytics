<?php
	set_include_path('../');
	ini_set('display_errors', '0');
?>

<html>
	<head>
		<script type="text/javascript" src="../../flight/web/js/jquery-1.7.1.min.js"></script>
	
		<script type="text/javascript">
			var files = [		'LoginSession_login.php',
								'patientList_search_returnsCorrectAmountOfRows.php',
								'unit/_SqlRec_ReservedOracleWords_ConvertsWord.php',
								'unit/util_quote_properlyEscapesStrings.php',
								'apiLogin_ReturnsNoErrors.php',
								'AssocArrayClass_QueryReturnsCorrectResult.php',
								//'unit/ccd_file_upload_uploadsFiles.php',
								//'unit/clinicalImport_ImportFromFile_Imports.php',
								'loginSession_FetchUser_isValidResult.php',
								'loginSession_fetchUserWithLogging_isValidResult.php',
								'loginSession_fetchUserWithLogging_ReadCorrectData.php',
								'Dao_query_returnsIntegerFromInsertQuery.php',
								'MsgInbox_countUnread_IsNotEmpty.php',
								'apiLogin_ReturnsNoErrors.php',
								'serverPatients_patientPageFetch_returnsObject.php',
								'uiFunctions_CONSTANTS_returnsJsonString.php' //VERY slow, takes about 12 seconds?.....
								/*'../tester.php?t=1',
								'../tester.php?t=2',
								'../tester.php?t=3',
								'../tester.php?t=4',
								'../tester.php?t=5'
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
								'../tester.php?t=25',
								'../tester.php?t=26',
								'../tester.php?t=27',
								'../tester.php?t=28',
								'../tester.php?t=29',
								'../tester.php?t=30',
								'../tester.php?t=31',
								'../tester.php?t=32',
								'../tester.php?t=33',
								'../tester.php?t=34',
								'../tester.php?t=35',
								'../tester.php?t=36',
								'../tester.php?t=37',
								'../tester.php?t=38',*/
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
		<b>* The tester.php?t=xxx tests will NOT WORK unless you have a valid login session established. To do this simply go to /sec and log in.</b>
		<br><br>
		<div id="results"></div>
	</body>
</html>