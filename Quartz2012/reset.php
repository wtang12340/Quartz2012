<?php

    session_start();

	$thisfilename = "reset.php";

    include 'dbvars.php';

    /*

    This script should be invoked only by the system -- specifically from the reset link created by forgot.php
    or as the submit action from the reset form below.

    When this script is invoked from reset link, the input arguments are

    GET['id'] (the user's email address encrypted with md5 preceded by the zero '0' character) and

    GET['sr'] (the value 'y' to indicate the call origin is the reset link).

    In this case the script uses the id to obtain the user's email address, and presents a form for entering a new password. [RESET.0001]  The submit action of this form calls this script with the input arguments GET['name'] (the user's email address), POST['pass'] (the new password) and POST['reset'] (the indicator that the reset button was clicked). [RESET.0002]

    When this script is invoked from the reset form below, the input arguments are

    GET['name'] (the user's email address),

    GET['sr'] (the value 'z' to indicate the call origin is the reset form),

    POST['pass'] (the new password) and

    POST['reset'] (the indicator that the reset button was clicked).

    In this case the script simply validates the input and stores the new password (in md5 encrypted form) in the nLogin table. [RESET.0003]

	The call origin value held in GET['sr'] is deliberately obfuscated by using the relatively-meaningless argument name 'sr' and the values 'y' and 'z'.  The security benefit of these cryptic values is deemed to outweigh the potential confusion these values may cause.

	Note that the id value passed in from the reset link is stored as a 'hash' value in the loginSet table when the reset link is created in forgot.php. [FORGOT.0001]  This 'hash' value is cleared from the loginSet table by the code below when the new password is stored in the nLogin table. [RESET.0004]

    */

    function rejectInvocation($rootpath)
    {
		print("ERROR: Incompatible browser.  Please install Internet Explorer 4.0.");
	}

    // check for presence of call origin

    $callorigin = "";

    if ( isset($_GET['sr']) )
    {
		$callorigin = $_GET['sr'];
	}
	else
	{
		die(rejectInvocation($rootpath)."@-1");
	}

	// $callorigin must be either 'y' (the reset link) or 'z' (the reset form)

	if ( ( $callorigin == 'y' || $callorigin == 'z' ) == FALSE )
	{
		die(rejectInvocation($rootpath)."@0");
	}

	// call origin is ok; now perform the requested task

	if ( $callorigin == "z" ) // from reset form; check for proper input, and then reset the password // [RESET.0003]
	{
		if ( ( isset($_GET['name']) && ( $_GET['name'] != "" ) )
			&& ( isset($_POST['pass']) && ( $_POST['pass'] != "" ) )
			&& ( isset($_POST['reset']) && ( $_POST['reset'] != "" ) ) )
		{
			// Connect to DB server

			mysql_connect($serverurl, $adminname, $adminp) or die(mysql_error());
			mysql_select_db($dbname) or die(mysql_error());

			// Set the new password for the user

			mysql_query("UPDATE loginSet SET hash = ''  WHERE email = '". $_GET['name']. "'") // [RESET.0004]
				or die(mysql_error());

			mysql_query("UPDATE nLogin SET password = '".md5($_POST['pass'])."'  WHERE email = '". $_GET['name']. "'")
				or die(mysql_error());

			die("Your password has been reset.<br><a href='". $rootpath . "'>Click to log in</a>");
		}
		else
		{
			die(rejectInvocation($rootpath)."@1");
		}
	}
	else if ( $callorigin == "y" ) // from reset link; get user info and then display the reset form // [RESET.0001]
	{
		if ( isset($_GET['id']) && ($_GET['id']!= "") )
		{
			$id = substr($_GET['id'], 1); // strip off the prepended 0

			// Connect to DB server

			mysql_connect($serverurl, $adminname, $adminp) or die(mysql_error());
			mysql_select_db($dbname) or die(mysql_error());

			// Select user information from Database

			$query = "SELECT * FROM loginSet WHERE hash = '".$id."'";

			// print("[".$query."]<br>");

			$check = mysql_query($query)or die(mysql_error());
			$info = mysql_fetch_array( $check );

			if (!$info)
			{
				die(rejectInvocation($rootpath)."@2");
			}


			$headerincludedfrom = "reset.php";

            include 'header.php';

			?>

			<!-- present the form -->

			<center>

			<div style="position: relative; width: 945px; height: 400px; background: url('images/Body.jpg');">
			<div style="position: relative; font-family:Tahoma; font-size:14px; top: 50px; background: url('images/Login.jpg'); width: 525px; height: 246px;">

				<br><br><br><br><br><br>Email : <?php echo $info['email']; ?><br><br>

				<form action="reset.php?name=<?php echo $info['email']; ?>&sr=z" method="POST">  <!-- [RESET.0002] -->
				New Password : <input type="password" name="pass" value="" size="20" /><br><br>
				<br><br>       <input type="submit" value="Reset Password" name="reset" />
				</form>

			</div>
			</div>

			</center>

			<!-- present the form -->

			<?php

			include 'footer.php';

		}
		else
		{
				die(rejectInvocation($rootpath)."@2.1");
		}
	}

?>


