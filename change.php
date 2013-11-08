<?php

	/*
		Author: Created by Mathew Baltes
		Updated: August 2, 2011
		Description: This php script's main functionality is to allow the user to change their postings.
		Intellectual Copyright established May 15th, 2011
	*/

	function error_msg()
	{
		//How did the user get here? Must've been up to no good.
		echo "<div class='invalid' align='center'>Error: Invalid information.</div><br>
			<div align='center'>If you feel you reached this in error, please contact webmaster@footesbooks.com</div>";
	}

	require("./include/header.php");
	require("./include/config.php");
	require("./include/functions.php");
	if ($_GET[id] > 0)
	{
		$id = num_only($_GET[id]);
		
		//connects to the database
		mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
		mysql_select_db($dbname) or die(mysql_error());
		
		$userResult = mysql_query("SELECT * FROM submissions WHERE id = '$id'") or die(mysql_error());
		
		$row = mysql_fetch_array( $userResult );
		
		$string = $row[id].$row[email];
		$salt1 = 'REMOVED'; //REMOVED for privacy issues.
		$salt2 = 'REMOVED'; //REMOVED for privacy issues.
		$hash = md5($salt1.$string.$salt2);
		//echo $hash;

		echo "<br><hr>";

		if ($_GET[uid] == $hash)
		{
			$special = $condition = $price = 0;
			$isValidPrice = false;
			

			if (is_numeric($_POST[price]) && $_POST['submitted'] == 1)	
			{
				$isValidPrice = true;
				$_POST[price] = number_format($_POST[price], 2, '.', '');
			}


			if ($_GET[action] == "delete")
			{
				$query = "DELETE FROM submissions WHERE id = '$id'";
				mysql_query($query) or die(mysql_error());
				echo "<br>";
				echo "<div align='center'>";
				echo "<h4 class='valid'>Your submission has been deleted.</h4>";
				echo "<br>";
				echo "<h4>Thank you for posting with us!</h4>";
				echo "<br>";
				
				$to = $row[email];
				$subject = "FootesBooks.com Book Posted";
				$from = "noreply@footesbooks.com";
				$body = "Your book posting has been deleted by your request.\n
If you have any feedback you would like to leave, please visit: http://www.footesbooks.com/feedback.php
				
Thank you for posting with FootesBooks.com!";
				$headers = "From: $from";
				mail($to, $subject, $body, $headers);

			}			
			elseif ($isValidPrice && $_POST['submitted'] == 1)
			{
				$_POST[special] = $_POST[special] - 1;
				$query = "UPDATE `mathewb_book`.`submissions` SET `price` = '$_POST[price]', `condition` =  '$_POST[condition]', `special` =  '$_POST[special]' WHERE `submissions`.`id` = $_GET[id]";
				//echo $query;
				mysql_query($query) or die(mysql_error());
				echo "<br>";
				echo "<div align='center'>";
				echo "<h4 class='valid'>Your submission has been updated.</h4><hr width='300'>";
				echo "<table border='0'><tr><td>";
				if ($_POST[special] > 0)
					echo "School Specific Edition: Yes";
				else
					echo "School Specific Edition: No";
					
				echo "<br>Condition: ";
				switch ($_POST[condition])
				{
					case 1: 
						echo "New";
						break;
					case 2:
						echo "Like New";
						break;
					case 3:
						echo "Very Good";
						break;
					case 4:
						echo "Good";
						break;
					case 5:
						echo "Fair";
						break;
					case 6:
						echo "Poor";
						break;
				}
				echo "<br> Price: $$_POST[price] <br>";
				echo "</table><br><hr width='400'>";
				echo "<h3>Thanks again for posting with us!</h3><br></div>";
			}
			else
			{
				echo "<div align='right'><a href='http://www.footesbooks.com/change.php?id=$id&uid=$hash&action=delete'><h4>Delete Posting</h4></a></div>";
				if ($_POST[condition] == 0)
				{
					$_POST[condition] = $row[condition];
				}
				if ($_POST[price] == 0)
				{
					$_POST[price] = $row[price];
				}
				if ($_POST[special] == 0)
				{
					$_POST[special] = $row[special];
					$_POST[special] = $_POST[special] + 1;
				}
				//echo "User has been authenticated.";
				echo "<br>
						<form action='change.php?id=$id&uid=$_GET[uid]' method='post' name='submitForm'>
						<div align='center'><table width='400' align='center'>
						Change Posting Information
						<hr width='400'><br>";
				echo "<tr><td>Book Condition: </td><td><select name='condition'>";
				
				if ($_POST[condition] == 1)
				{
					echo "<option value='1' selected='selected'>New</option>";
				}
				else {echo "<option value='1'>New</option>";}
				if ($_POST[condition] == 2)
				{
					echo "<option value='2' selected='selected'>Like New</option>";
				} else {echo "<option value='2'>Like New</option>";}
				if ($_POST[condition] == 3)
				{
					echo "<option value='3' selected='selected'>Very Good</option>";
				} else {echo "<option value='3'>Very Good</option>";}
				if ($_POST[condition] == 4)
				{
					echo "<option value='4' selected='selected'>Good</option>";
				}
				else {echo "<option value='4'>Good</option>";}
				if ($_POST[condition] == 5)
				{
					echo "<option value='5' selected='selected'>Fair</option>";
				}
				else {echo "<option value='5'>Fair</option>";}
				if ($_POST[condition] == 6)
				{
					echo "<option value='6' selected='selected'>Poor</option>";
				} else {echo "<option value='6'>Poor</option>";}
					
				echo "</select></td></tr>";
				echo "<tr><td>School Specific Edition: </td><td><select name='special'>";
				if ($_POST[special] == 1)
				{
					echo "<option value='1' selected='selected'>No</option>";
				}
				else {echo "<option value='1'>No</option>";}
				if ($_POST[special] == 2)
				{
					echo "<option value='2' selected='selected'>Moorpark College</option>";
				} else {echo "<option value='2'>Moorpark College</option>";}
				if ($_POST[special] == 3)
				{
					echo "<option value='3' selected='selected'>Ventura College/option>";
				} else {echo "<option value='3'>Ventura College</option>";}
				if ($_POST[special] == 4)
				{
					echo "<option value='4' selected='selected'>Oxnard College</option>";
				}
				else {echo "<option value='4'>Oxnard College</option>";}
			
				echo "
					</select></td></tr><tr><td>Price:</td><td>";
					if ($isValidPrice)
					{
						echo "<input name='price' type='text' class='valid' value='$_POST[price]'>";
					}
					else if ($_POST['submitted'] == 1)
						echo "<input name='price' type='text' class='invalid' value='$_POST[price]'>";
					else
						echo "<input name='price' type='text' value=$_POST[price]>";
				echo "</td></tr></table>";
				echo "<input type='hidden' name='submitted' value='1'>
				<br><input type='submit' value='Submit Changes' class='subButton'></div></form><br>";
			}
		}
		else
		{
			error_msg();
		}
	}
	else
	{
		error_msg();
	}
	echo "<br>";
	require("./include/footer.php");
?>
