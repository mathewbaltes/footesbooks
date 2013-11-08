<?php
	/*
		Author: Created by Mathew Baltes
		Updated: August 2, 2011
		Description: This script is just a simple feedback, nothing special.
		Intellectual Copyright established May 15th, 2011
	*/


	require("./include/header.php");
	echo "
	<hr width='100%'>
	<div align='center'>
		Feedback Form
		<hr width='300'>
		<br>
		<table width='98%' border='0'>
		<tr><td>";
			if ($_POST['submitted'] == 0)
			{
				echo "
				<div align='center'>
				<form method='POST' action='feedback.php'>
					Email: <input type='text' name='email' size='19'><br><br>Comments: 
					<br>
					<textarea rows='9' name='message' cols='30'></textarea>
					<br>
					<br>
					<input type='hidden' name='submitted' value='1'>
					<input type='submit' value='Submit' name='submit' class='subButton'>
				</form>
				<br>
				</div>";
			}
			else
			{
				$to = "webmaster@footesbooks.com";
				$subject = "Feedback";
				$name = 'Anonymous';
				$email = $_POST['email'];
				$message = $_POST['message'];
				 
				$body = "E-Mail: $_POST[email]\nMessage:\n $message";
				 
				echo "<div align='center'><h4 class='valid'>Feedback submitted.</h4><br><h3>Thank you for sending us your feedback!</h3><br></div>";
				mail($to, $subject, $body);
			}
		echo "
		</td></tr>
		</table>
	</div>";
	require("./include/footer.php");
?>