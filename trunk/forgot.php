<?php
	if ($_GET['q'] && $_GET['q'] == 'expired') {
		echo 'Sorry. This link has been expired.';
	}
	else {
		echo '
		<script>
			function validate() {
				var a = document.forms["passwordForm"]["newPassword"].value;
				if (a && a.length >= 8) {
					return true;
				} else {
					alert("Your new password has to be at least 8 characters.");
					document.forms["passwordForm"]["newPassword"].value = "";
					return false;
				}
			}
		</script>
		<form name="passwordForm" action="backend/post.php" method="post" onsubmit="return validate()" >
			<input type="hidden" name="r" value="updateForgottenPassword" />
			New password (at least 8 characters): <input type="password" name="newPassword" />
			<input type="submit" value="Submit" />
		</form>';
	}
?>


