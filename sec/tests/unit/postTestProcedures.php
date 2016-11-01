<b>
<?php
	//We don't close connections here.
	
	if ($testPassed) {
		echo '<span style="color: green;">Test passed!</span>';
	}
	else {
		http_response_code(401);
		echo '<span style="color: red;">Test failed!</span>';
	}
?>
</b>

<br><br>

Script took <?php echo floor(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]); ?> seconds to complete.