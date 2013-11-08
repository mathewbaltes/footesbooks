<html>
<head>
	<title>User Contact Form</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<?php

	/*
		Author: Created by Mathew Baltes
		Updated: August 2, 2011
		Description: This script's main functionality is to allow users to contact the poster while allowing the 
					 poster to maintain anonymity.
		Intellectual Copyright established May 15th, 2011
	*/

	require("./include/config.php");
	
	if ($_GET[postid] > 0)
	{
		if ($_POST['submitted'] == 0)
		{
			echo "
			<br>
			<div align='center'>
			<form method='POST' action='contact.php?postid=$_GET[postid]'>
				Email: <input type='text' name='email' size='25'><br><br>Message: 
				<br>
				<textarea rows='9' name='message' cols='30'></textarea>
				<br>
				<br>
				<input type='hidden' name='submitted' value='1'>
				<input type='submit' value='Submit' name='submit' class='subButton'>
			</form>
			<div align='right'><a href='javascript:window.close()'><h5>CLOSE WINDOW</h5></a></div>
			</div>";
		}
		else
		{
			//connects to the database
			mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
			mysql_select_db($dbname) or die(mysql_error());
			
			$bookResult = mysql_query("SELECT * FROM submissions WHERE id = '$_GET[postid]'") or die(mysql_error());
			
			//First query
			$row = mysql_fetch_array( $bookResult );
			$to = $row['email'];
			$isbn = $row['isbn'];
			
			if (strlen($isbn) == 10)
				$bookResult = mysql_query("SELECT * FROM books WHERE isbn = '$isbn'") or die(mysql_error());
			else
				$bookResult = mysql_query("SELECT * FROM books WHERE isbn13 = '$isbn'") or die(mysql_error());
			//echo "SELECT * FROM books WHERE isbn = '$isbn'";
			$row = mysql_fetch_array( $bookResult );
			$title = $row['title'];
			
			$subject = "FootesBooks.com - User contacting about posting: $title";
			$from = $_POST['email'];
			$message = $_POST['message'];
			$body = "$message\n
			
//FootesBooks.com Notice//\n
//Your email will remain private unless you respond to this user.//\n
//Be wary of some payments such as cashier checks and wiring.//\n";
			$headers = "From: $from";
			mail($to, $subject, $body, $headers);
			
			echo "<div align='center'><h4 class='valid'>User contacted about listing.</h4><br><h4>Check your own email for when the listing owner replies.</h4><br></div>";
			echo "<br><div align='right'><a href='javascript:window.close()'><h5>CLOSE WINDOW</h5></a></div>"; 
		}
	}
?>
</body>
</html>