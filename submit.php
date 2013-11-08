<?php

	/*
		Author: Created by Mathew Baltes
		Updated: August 2, 2011
		Description: This php script is the main submission page. It validates user entry so when it makes it to the db, 
					 the data is legit.
		Intellectual Copyright established May 15th, 2011
	*/

	require("./include/header.php");
	require("./include/config.php");
	require("./include/functions.php");
	include_once("./include/isbn.php");
	
	//vars
	$isbn = $special = $condition = $price = $email = $phone = 0;
	$isValidIsbn = $isValidPrice = $isValidEmail = false;
	$isbnTmp = new ISBN;
	$accessKey = "REMOVED"; //REMOVED for privacy issues.
	
	if (is_numeric($_POST[price]) && $_POST['submitted'] == 1)	
	{
		$isValidPrice = true;
		$_POST[price] = number_format($_POST[price], 2, '.', '');
	}
	$isbnTmp->set_isbn(num_only($_POST[isbn]));
	if ($isbnTmp->valid_isbn10() == TRUE || $isbnTmp->valid_isbn13() == TRUE)
	{
		$isValidIsbn = true;
	}
	if (is_valid_email($_POST[email]))
	{
		$isValidEmail = true;
	}
	
	$phone = num_only($_POST[phone]);
	/*
	if (strlen($tmpPhone) == 10 || strlen($tmpPhone) == 7)
	{
		$isValidPhone = true;
	}
	*/
	if ($isValidIsbn && $isValidPrice && $isValidEmail)
	{
		//connects to the database
		mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
		mysql_select_db($dbname) or die(mysql_error());
	
		$title = $authors = $edition = 0;
		$isbn = num_only($_POST[isbn]);
		$price = number_format($_POST[price], 2, '.', '');
		$email = $_POST[email];
		$condition = $_POST[condition];
		$special = $_POST[special];
		$special = $special - 1;
		$date = num_only(date("mdY"));
		
		$bookResult = mysql_query("SELECT * FROM books WHERE isbn = '$isbn' OR isbn13 = '$isbn'") or die(mysql_error());
		
		if (mysql_num_rows($bookResult) == 0)
		{
			// Reinserted code from include to show functionality
			// Imported ISBN methods from library, thanks Dawson.
			$url_details = "http://isbndb.com/api/books.xml?access_key=$accessKey&results=details&index1=isbn&value1=$isbn";
			$url_prices = "http://isbndb.com/api/books.xml?access_key=$accessKey&results=prices&index1=isbn&value1=$isbn";     
			
			$xml_prices = @simplexml_load_file($url_prices) or die ("no file loaded") ;
			$xml_details = @simplexml_load_file($url_details) or die ("no file loaded") ;
				
			// Parse Data
			$isbn10 = $xml_prices->BookList[0]->BookData[0]['isbn'];
			$isbn13 = $xml_prices->BookList[0]->BookData[0]['isbn13'];
			$title =  $xml_prices->BookList[0]->BookData[0]->TitleLong;
			if (strlen($title) < 3)
				$title =  $xml_prices->BookList[0]->BookData[0]->Title;
			$authors = $xml_prices->BookList[0]->BookData[0]->AuthorsText;
			$edition = $xml_details->BookList[0]->BookData[0]->Details[0]['edition_info'];
			if (strlen($title) > 0)
				mysql_query("INSERT INTO books (isbn, isbn13, title, author, edition) VALUES ('$isbn10', '$isbn13', '$title','$authors','$edition')") or die(mysql_error());
		}
		else
		{
			$row = mysql_fetch_array( $bookResult );
			$title = $row['title'];
			$authors = $row['author'];
			$edition = $row['edition'];
		}	
		
		$bookResult = mysql_query("INSERT INTO `mathewb_book`.`submissions` (`id`, `email`, `isbn`, `phone`, `price`, `condition`, `special`,`paypal`, `date`) 
								   VALUES (NULL, '$email', '$isbn', '$phone', '$price', '$condition', '$special', '0', '$date')") or die(mysql_error());
		
		$id = mysql_insert_id();
		
		$string = $id.$email;
		$salt1 = 'REMOVED'; //REMOVED for privacy issues.
		$salt2 = 'REMOVED'; //REMOVED for privacy issues.
		$hash = md5($salt1.$string.$salt2);
		
		$to = $email;
		$subject = "FootesBooks.com Book Posted";
		$from = "noreply@footesbooks.com";
		$body = "Thank you for posting with FootesBooks.com!\n
If you need to change your posting (pricing, condition, etc.) or delete it, click on the link below.
http://www.footesbooks.com/change.php?id=$id&uid=$hash

//FootesBooks.com Notice//\n
//Be wary of some payments such as cashier checks and wiring.//\n";
		$headers = "From: $from";
		mail($to, $subject, $body, $headers);
		
		echo "<br><hr width='600'><br>";
		echo "<div align='center'>";
		echo "<h4 class='valid'>Your submission below has been posted on the search.</h4><hr width='300'>";
		echo "<table border='0'><tr><td>";
		echo "Title: $title <br> Author: $authors <br> Edition: $edition<br>";
		if ($special > 0)
			echo "School Specific Edition: Yes";
		else
			echo "School Specific Edition: No";
		echo "<br> ISBN: $isbn <br>Condition: ";
		switch ($condition)
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
		echo "<br> Price: $$price <br> Email: $email <br>";
		if (strlen($phone) == 7 || strlen($phone) == 10)
			echo "Phone: $phone <br>";
		echo "</table><br><hr width='400'>";
		echo "<h3>Thanks for posting with us!</h3><br></div>";
	}
	else
	{
		echo "<hr width='100%'>
		<form action='submit.php' method='post' name='submitForm'>
			<div align='center'>
			<h4 class='notice'>Notice: * (Required information)</h4><br>
			Book Information
			<hr width='400'>
			<table width='400' border='0'>
			<tr>
			<td>*ISBN: </td><td>";
		if ($_POST['submitted'] == 1) 
		{
			if ($isValidIsbn)
			{
				echo "<input type='text' name='isbn' class='valid' value='$_POST[isbn]'>";	
			}
			else
				echo "<input type='text' name='isbn' class='invalid' value='$_POST[isbn]'>";
		}
		else
			echo "<input type='text' name='isbn'>";
			
		echo "</td></tr><br />";
		echo "<tr><td>*Book Condition: </td><td><select name='condition'>
		";
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
			</select></td></tr>
			<br>
			<tr><td>*Asking Price ($): </td><td>";
		if ($isValidPrice)
		{
			echo "<input name='price' type='text' class='valid' value='$_POST[price]'>";
		}
		else if ($_POST['submitted'] == 1)
			echo "<input name='price' type='text' class='invalid' value='$_POST[price]'>";
		else
			echo "<input name='price' type='text'>";
		echo "</td></tr>
			</table></div><br>
			<div align='center'>Personal Information: (Used in listing. Email is private.)
			<hr width='400'><table width='400' border='0'>
			<tr><td>*Email: </td><td>";
		
		if ($_POST['submitted'] == 1)
		{
			if ($isValidEmail)
			{
				echo "<input name='email' type='text' class='valid' value='$_POST[email]'>";
			}
			else
				echo "<input name='email' type='text' class='invalid' value='$_POST[email]'>";
		}
		else
			echo "<input name='email' type='text'>";
		echo "</td></tr>
			<br>
			<tr><td>Phone Number (Optional): </td><td>";
		/*
		if ($_POST['submitted'] == 1)
		{
			if ($isValidPhone)
			{
				echo "<input name='phone' type='text' class='valid' value='$_POST[phone]'>";
			}
			else
				echo "<input name='phone' type='text' class='invalid' value='$_POST[phone]'>";
		}
		else*/
			echo "<input name='phone' type='text'>";
		
		echo "</td></tr>
			</table>
			<input type='hidden' name='submitted' value='1'>
			<br><input type='submit' value='Submit' class='subButton'></div>
		</form><br>
		";
	}
	require("./include/footer.php");
?>