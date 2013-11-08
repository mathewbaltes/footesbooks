<?php
	/*
		Author: Created by Mathew Baltes
		Updated: August 2, 2011
		Description: This php script is a search engine that searches relevant books based on ISBN, Author or Title. 
					 It interfaces with a mysql database to update and retreive books on demand.
					 It uses the ISBNDB.com API to get detailed information based on a books ISBN number which it stores in the database
					 for future references and requests.
		Intellectual Copyright established May 15th, 2011
	*/
	
	require("./include/header.php");
	require("./include/config.php");
	require("./include/functions.php");
	include_once("./include/isbn.php");
	
	$accessKey = "REMOVED"; //REMOVED for privacy issues.
	$userInput = $_POST['userInput'];
	$userInput = str_replace(".", "", $userInput); //Removes periods to prevent injection
	$userInput = str_replace("-", "", $userInput); //Removes hyphens to prevent injection
	$userInput = trim($userInput);
	
	function throw_ex($er){  
		throw new Exception($er);  
	}  
	
	function display($bookResult)
	{
	    while($row = mysql_fetch_array( $bookResult )) 
		{
			echo "<table border='1' bordercolor='000000' style='background-color:#FFFFCC' width='99%' cellpadding='3' cellspacing='1' class='ctable'>";
			echo "<tr> <th>ISBN</th> <th>Title</th> <th>Author</th> <th>Edition</th> </tr>";
			
			// Displays every search listing
			echo "<tr><td width='165'>ISBN10: ";
			if (strlen($row['isbn']) == 9)
			{
				echo "0";
				echo $row['isbn'];
			}
			else
				echo $row['isbn'];
			echo "<br>ISBN13: ";
			echo $row['isbn13'];
			echo "</td><td><div align='center'>"; 
			$title = $row['title'];
			echo $title;
			echo "</div></td><td><div align='center'>";
			if (strlen($row['author']) > 0)
			{
				$author = $row['author'];
				echo $author;
			}
			else
			{
				echo "N/A";
			}
			echo "</div></td><td><div align='center'>"; 
			if (strlen($row['edition']) > 0)
			{
				$edition = $row['edition']; 
				echo $edition;
			}
			else
			{
				echo "N/A";
			}
			echo "</td></tr></div>";
			echo "</table>";
			
			echo "<br>";
			
			//echo "SELECT * FROM submissions WHERE isbn = $isbn";
			$userResult = mysql_query("SELECT * FROM submissions WHERE isbn = $row[isbn] OR isbn = $row[isbn13]") or die(mysql_error());
			if (mysql_num_rows($userResult) > 0)
			{
				//echo "<h3>Results for ISBN: ".$isbn." and Title: ".$title;
				echo "<table border='1' bordercolor='000000' style='background-color:#FFFFCC' width='99%' cellpadding='3' cellspacing='1' class='ctable'>";
				echo "<tr> <th>Price</th> <th>Condition</th> <th>School Specific Edition</th> <th>Contact Info</th> <th>Date Posted</th></tr>"; /*<th>Accepts Paypal</th>*/
				while($row = mysql_fetch_array( $userResult )) 
				{
					// Displays every search listing
					echo "<tr><td><div align='center'>"; 
					echo "$".number_format($row['price'], 2, '.', '');
					echo "</div></td><td><div align='center'>"; 
					switch ($row['condition'])
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
					echo "</div></td><td><div align='center'>"; 
					switch ($row['special'])
					{
						case 1: 
							echo "Yes, Moorpark College";
							break;
						case 2:
							echo "Yes, Ventura College";
							break;
						case 3:
							echo "Yes, Oxnard College";
							break;
						default:
							echo "No";
							break;
					}
					echo "</div></td><td><div align='center'>"; 
					//echo $row['phone'];
					if (strlen($row['phone']) == 7)
					{
						echo substr($row['phone'], 0, 3);
						echo "-";
						echo substr($row['phone'], 3, 4);
						echo "<br>";
					}
					elseif (strlen($row['phone']) == 10)
					{
						echo substr($row['phone'], 0, 3);
						echo "-";
						echo substr($row['phone'], 3, 3);
						echo "-";
						echo substr($row['phone'], 6, 4);
						echo "<br>";
					}
					//$id = $row['id'];
					$url = "./contact.php?postid=$row[id]";
					$info = "window.open('$url','contact','width=400,height=300,left=0,top=100,screenX=0,screenY=100')";
					echo "
					<FORM>
						<INPUT type='button' value='Email Poster' class='button' onClick=\"$info\">
					</FORM>
					";
					//echo "<a href='./contact.php?postid=$row[id]' target='_blank'>Contact User by Email</a>";
					//echo substr($row['phone'], 0, 2) + substr($row['phone'], 3, 5) + substr($row['phone'], 6);
					echo "</div></td>";
					echo "<td><div align='center'>";
					/* Possible paypal support?
					if ($row['paypal'])
						echo "Yes";
					else
						echo "No";
					echo "</div></td><td><div align='center'>"; 
					*/
					if (strlen($row['date']) == 7) //Fix int errors from mysql
					{
						$date = "0";
						$date = $date.substr($row['date'], 0, 1)."/".substr($row['date'], 1, 2)."/".substr($row['date'], 3);
					}
					
					echo $date;
					echo "</td></tr></div>";
				}
				echo "</table>";
				echo "<br><hr width='400' align='center'>";
			}
			else
			{
				echo "<table border='1' bordercolor='000000' style='background-color:#FFFFCC' width='99%' cellpadding='3' cellspacing='1' class='ctable'>";
				echo "<tr><td><div align='center'>"; 
				echo "No submissions found for ISBN10: '$row[isbn]' or ISBN13: '$row[isbn13]' and Title: ".$title;
				echo "</div></td></tr></table>";
				echo "<br><hr width='400' align='center'>";
			}
			echo "<br>";
			echo "<br>";
		}
	}
	
	//connects to the database
	mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
	mysql_select_db($dbname) or die(mysql_error());
	
	//echo "<br><hr width='600'>";
	echo "<div align='center'>";
	if (strlen($userInput) >= 3 && $_POST['searched'] == 1)
	{	
		$isbnTmp = new ISBN();
		$isbnTmp->set_isbn($userInput);
		
	    if (is_numeric($userInput))
		{	
			if ($isbnTmp->valid_isbn10() && strlen($userInput) == 10)
			{
				$userInputType = "isbn10";
			}
			elseif ($isbnTmp->valid_isbn13() && strlen($userInput) == 13)
			{
				$userInputType = "isbn13";
			}
		}
		
		if ($userInputType == "isbn10" || $userInputType == "isbn13")
		{	
			//echo $userInput;
			//echo "<br>";
			if ($userInputType == "isbn10")
			{
				$bookResult = mysql_query("SELECT * FROM books WHERE isbn = '$userInput'") or die(mysql_error());
				//echo "SELECT * FROM books WHERE isbn = '$userInput'";
			}
			else
			{
				$bookResult = mysql_query("SELECT * FROM books WHERE isbn13 = '$userInput'") or die(mysql_error());
				//echo "SELECT * FROM books WHERE isbn13 = '$userInput'";
			}
		
			if (mysql_num_rows($bookResult) == 0)
			{
				//Reinserted code from include to show functionality
				// Imported ISBN methods from library, thanks Dawson.
				$url_details = "http://isbndb.com/api/books.xml?access_key=$accessKey&results=details&index1=isbn&value1=$userInput";
				$url_prices = "http://isbndb.com/api/books.xml?access_key=$accessKey&results=prices&index1=isbn&value1=$userInput";     
				
				
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
				
				//Retrieve again after adding it to the book database. It is done this way to save # of API access queries to isbndb
				$bookResult = mysql_query("SELECT * FROM books WHERE isbn = '$isbn10' OR isbn13 = '$isbn13' ") or die(mysql_error());
			}
			display($bookResult);
		}
		else
		{
			try {
				$bookResult = mysql_query("SELECT * FROM books WHERE title LIKE '%".$userInput."%' OR author LIKE '%".$userInput."%'") or throw_ex(mysql_error()); 
			}
			catch(exception $e) {
			}

			if ($bookResult == false)
			{
				echo "<div class='invalid'>Invalid query.</div><br><br>";
			}
			elseif (mysql_num_rows($bookResult) == 0)
			{
				echo "<div class='invalid'>Nothing found.</div><br><br>";
			}
			else
			{
				display($bookResult);
			}
		}
	}
	else
	{	
		if ($_POST['searched'] == 1)
		{
			echo "<div class='invalid'>Please search using 3 or more characters.</div><br>";
		}
	}
	echo "</div>";
	require("./include/footer.php");
?>