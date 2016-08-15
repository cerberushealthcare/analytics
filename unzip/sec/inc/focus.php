<script type="text/javascript" language="JavaScript">
<!--
	var focusControl = document.forms[0].elements["<?=$focus?>"];
	if (focusControl != null && focusControl.type != "hidden") {
		if (focusControl.type == "text") {
			focusControl.select();
		} 
		focusControl.focus();
	}
// -->
</script>