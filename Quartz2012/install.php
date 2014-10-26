<?php
//Installation script for Quartz

$thisfilename = "install.php";



//Form that user sees and fills up
?>
<html>
<body>

<p>Welcome to Quartz Installation, please enter corresponding information.</p>
<form action="install.php" method="post">
	Server Name: <input type="text" name="servername"><br>
	Root Password: <input type="password" name="rootpass">(If no password for root, leave blank.)<br>
	Admin Name: <input type="text" name="adname"><br>
	Admin Password: <input type="password" name="adpass"><br>
	Database Name: <input type="text" name="dataname"><br>
	Server Url: <input type="text" name="serverl"> (Enter Port as well with ":" if not default. For example" http://localhost:81/ instead of just http://localhost/)<br>
	Subfolder: <input type="text" name="subfolder"> (Do not put slash in the front but must put one in the back.)<br>
	Ability to Mail: <input type="radio" name="abmail" value="yes">Yes <input type="radio" name="abmail" value="no">No </br>
<input type="submit" name="submitted">
</form>

</body>
</html>

<?php

// Once user clicks submit the following happens

// Stores the variables with values entered in the form
if (isset($_POST['submitted']) && isset($_POST['abmail'])){
	$serverurl = $_POST["servername"];
	$adminname = $_POST["adname"];
	$adminp = $_POST["adpass"];
	$dbname = $_POST["dataname"];
	$serverhost = $_POST["serverl"];
	$rootpath = $serverhost.$_POST["subfolder"];
	$canmail = "false";
	if($_POST["abmail"]=="yes"){
		$canmail = "true";
	}

// Writes a new dbvars file from scratch	
	$db = fopen("dbvars.php",'w') or die("can't open file");
	fwrite($db, "<?php \n");
	fwrite($db, "$"."serverurl = "."\"".$serverurl."\"".";"."\n");
	fwrite($db, "$"."adminname = "."\"".$adminname."\"".";"."\n");
	fwrite($db, "$"."adminp = "."\"".$adminp."\"".";"."\n");
	fwrite($db, "$"."dbname = "."\"".$dbname."\"".";"."\n");
	fwrite($db, "$"."serverhost = "."\"".$serverhost."\"".";"."\n");
	fwrite($db, "$"."rootpath = "."\"".$rootpath."\"".";"."\n");
	fwrite($db, "$"."canmail = ".$canmail.";"."\n");
	fwrite($db, "$"."q = "."\""."\\"."\""."\"".";"."\n");
	fwrite($db, "?>");
	
// Connects to MYSQL 
	$con = mysql_connect($serverurl,"root",$_POST["rootpass"]);
	if (!$con){
		die('Could not connect: ' . mysql_error());
	}
	
// Creates the database	
	if (mysql_query("CREATE DATABASE $dbname",$con))
	{
		echo "Database created.   ";
		echo "<br>";
	}else
	{
		echo "Error creating database: " . mysql_error();
	}

// Grants permissions to admin and sets the username and password	
	mysql_query("USE $dbname;");
	mysql_query("GRANT ALL ON $dbname.* TO '$adminname'@'$serverurl';");
	mysql_query("SET PASSWORD FOR '$adminname'@'$serverurl' = PASSWORD('$adminp');");	
	mysql_close($con);
	
// Reconnects with admin username and password in order to set up the tables	
	$conn = mysql_connect($serverurl,$adminname,$adminp) or die("Unable to connect to MySQL server");
	mysql_select_db($dbname) or die( "Unable to select database");
	
// Queries of table setup	
	$query1 = "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";";
	$query2 = "CREATE TABLE `loginSet` (
	`email` varchar(40) NOT NULL,
	`isApproved` int(5) NOT NULL default '0',
	`hash` varchar(100) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	$query3 = "INSERT INTO `loginSet` (`email`, `isApproved`, `hash`) VALUES('".$adminname."@bu.edu', 1, '');";
	$query4 = "CREATE TABLE `nLogin` (
	`email` varchar(40) collate ascii_bin NOT NULL,
	`password` varchar(100) collate ascii_bin NOT NULL,
	`name` varchar(40) collate ascii_bin NOT NULL,
	`buid` varchar(9) collate ascii_bin NOT NULL,
	`isactive` tinyint(1) NOT NULL,
		PRIMARY KEY  (`email`)
		) ENGINE=MyISAM DEFAULT CHARSET=ascii COLLATE=ascii_bin;";
	$query5 = "INSERT INTO `nLogin` (`email`, `password`, `name`, `buid`, `isactive`) VALUES('".$adminname."@bu.edu', '".md5($adminp)."', 'admin', 'U00000000', 2);";
	$query6 = "CREATE TABLE `webData` (
	`email` varchar(40) NOT NULL,
	`name` varchar(40) NOT NULL default 'Title. Name M. Last',
	`bio` varchar(1500) NOT NULL default 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, ...anim id est laborum.',
	`phone` varchar(15) NOT NULL default '(XXX) XXX-XXXX',
	`fax` varchar(40) NOT NULL default '(XXX) XXX-XXXX',
	`office` varchar(100) NOT NULL default '#XXX Street Name, BID-RMN <br> Boston, MA 02215, USA',
	`jobtitle` varchar(40) NOT NULL default 'Job Title Here',
	`ofhours` varchar(100) NOT NULL default 'Day TT:TT - TT:TT <br> Day TT:TT - TT:TT',
	`isonline` tinyint(1) NOT NULL default '0',
	`researchsum` varchar(2000) NOT NULL default 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, ...anim id est laborum.',
	`teaching` varchar(1000) NOT NULL,
	`reslink` varchar(100) NOT NULL,
	`awards` varchar(5) NOT NULL,
	`projects` varchar(5) NOT NULL,
	`students` varchar(5) NOT NULL,
	`personal` varchar(5) NOT NULL
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	$query7 = "INSERT INTO `webData` (`email`, `name`, `bio`, `phone`, `fax`, `office`, `jobtitle`, `ofhours`, `isonline`, `researchsum`, `teaching`, `reslink`, `awards`, `projects`, `students`, `personal`) VALUES('".$adminname."@bu.edu', 'Title. Name M. Last', 'Lorem ipsum dolor ... est laborum.', '(XXX) XXX-XXXX', '(XXX) XXX-XXXX', '#XXX Street Name, BID-RMN <br> Boston, MA 02215, USA', 'Job Title Here', 'Day TT:TT - TT:TT <br> Day TT:TT - TT:TT', 1, 'Lorem ipsum dolor sit ...anim id est laborum.', 'CS XXX : Course Title;; - Lorem ipsum dolor ... pariatur.;CS XXX : Course Title;; - Lorem ipsum dolor ... pariatur.;', '', '', '', '', '');"; 
	
//Running the queries	
	mysql_query($query1) or die("INSERT query failed: ".mysql_error());
	mysql_query($query2) or die("INSERT query failed: ".mysql_error());
	mysql_query($query3) or die("INSERT query failed: ".mysql_error());
	mysql_query($query4) or die("INSERT query failed: ".mysql_error());
	mysql_query($query5) or die("INSERT query failed: ".mysql_error());
	mysql_query($query6) or die("INSERT query failed: ".mysql_error());
	mysql_query($query7) or die("INSERT query failed: ".mysql_error());
	print ("PHP successfully connected to $dbname!      ");
	echo "<br>";
	print ("Tables created. Installation Complete.");
	echo "<br>";
	mysql_close($conn);
	}
//Installation complete 	
?>