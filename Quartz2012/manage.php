<center><?php//============================================================//Filename : manage.php//Variables ://  Local://      $info - Databse record for this website.//      $uname - Bu username of website owner.//  $_SESSION://      'usertype' - The type of user currently logged in.//      'name' - Name of logged in user.//      'email' - Email of logged in user.//      'buid' - BUID of logged in user.////============================================================// Comments End Here ===========================================include 'dbvars.php';$thisfilename = "manage.php";//print($thisfilename."@0 _SESSION['email']: ".$_SESSION['email']."<br>");//die("after ".$thisfilename."@0");// Get username$uname = substr($_SESSION['email'],0,strlen($_SESSION['email'])-7);// Connect to DB server//print("manage.php@1: about to connect to database server<br>");mysql_connect($serverurl, $adminname, $adminp) or die(mysql_error());//print("manage.php@1.1: connected to database server; about to select database<br>");mysql_select_db($dbname) or die(mysql_error());//print("manage.php@1.2: selected database<br>");// Select user information from Database$check = mysql_query("SELECT * FROM loginSet WHERE email = '". $uname. "@bu.edu'")or die(mysql_error());$info = mysql_fetch_array( $check );//user is approved then...$approved = $info['isApproved'];// print("manage.php@2.0: apparently got login info for uname=[".$uname."], ".// 	"email=[".$_SESSION['email']."]; is approved? [".$approved."]<br>");// If the user is a level 2 user (admin)if ($_SESSION['usertype'] == 2){    // If the admin is adding a user    if (isset($_POST['adduser']) && $_POST['adduser'])    {        mysql_query("INSERT INTO loginSet (email) VALUES ('".$_POST['email']."')");    }    // If the admin is approving a user send the user an email    if (isset($_POST['appuser']) && $_POST['appuser'])    {        $to = $_POST['user'];        $subject = "CS Website Approval";        $body = "Hi,\n\nClick on the link below to approve your CS account.\n\n".$rootpath."approve.php?id=".md5($_POST['user'])."\n\n";        $headers = "From: admin@cs.bu.edu\r\n"."X-Mailer: php";				//Session variable instantiated here for convenience 		$_SESSION['mailinfo'] = '<a href="mailto:'.$to.'?subject='.$subject.'&body='.$body.'">Send Approval</a>'; 		        if ( $canmail )        {			mail($to,$subject,$body,$headers);        	header('Location: link.php'); 	//directs to new page        }        else        {        	header('Location: link2.php');  //directs to new page        }        mysql_query("UPDATE loginSet SET hash = '".md5($_POST['user'])."' WHERE email = '".$_POST['user']."'");    }    // If the admin is deauthorizing a user    if (isset($_POST['deauthorizeuser']) && $_POST['deauthorizeuser'])    {        mysql_query("UPDATE loginSet SET isApproved = 0 WHERE email = '".$_POST['user']."'");        mysql_query("UPDATE loginSet SET hash = '' WHERE email = '".$_POST['user']."'");        mysql_query("UPDATE webData SET isonline = 0 WHERE email = '".$_POST['user']."'");    }    // If the admin is deleting a user    if (isset($_POST['deluser']) && $_POST['deluser'])    {        mysql_query("DELETE FROM loginSet WHERE email = '".$_POST['user']."'");        mysql_query("DELETE FROM nLogin WHERE email = '".$_POST['user']."'");        mysql_query("DELETE FROM webData WHERE email = '".$_POST['user']."'");    }    // Get Website data    $check = mysql_query("SELECT * FROM loginSet")or die(mysql_error());}//If the user is a level 1 user (professor)if ($_SESSION['usertype'] == 1){	//If the user has an approved account	if ($approved)	{		// Get Website data		$check = mysql_query("SELECT * FROM webData WHERE email = '". $uname. "@bu.edu'")or die(mysql_error());		$info = mysql_fetch_array( $check );		// If website record doesnt exist then create one		if (!$info)		{			mysql_query("INSERT INTO webData (email) VALUES ('". $uname. "@bu.edu')");			$check = mysql_query("SELECT * FROM webData WHERE email = '". $uname. "@bu.edu'")or die(mysql_error());			$info = mysql_fetch_array( $check );		}		// If someone is updating the online/offline status of the website		if (isset($_POST['websettings']) && $_POST['websettings'])		{			if ($info)			{				mysql_query("UPDATE webData SET isonline = ".$_POST['isonline']." WHERE email = '" . $uname. "@bu.edu'");				$info['isonline'] = $_POST['isonline'];			}			else			{				die('Internal Server Error : Code 101');			}		}		//If someone is uploading a picture		if (isset($_POST['upload']) && $_POST['upload'])		{			$target_path = $uname."/picture.jpg";			@unlink($target_path); // '@' prevents any error messages, such as 'the file does not exist'			if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path))			{				echo "<Script language='javascript'>alert ('Upload succeeded.')</script>";			}			else			{				echo "<Script language='javascript'>alert ('Upload failed.')</script>";			}		}		// If Someone is uploading general information		if (isset($_POST['geninfo']) && $_POST['geninfo'])		{			if ($info)			{				$info['name'] = $_POST['name'];				$info['phone'] = $_POST['tel'];				$info['fax'] = $_POST['fax'];				$info['ofhours'] = $_POST['ohrs'];				$info['jobtitle'] = $_POST['job'];				$info['office'] = $_POST['address'];				mysql_query("UPDATE webData SET name = '".$info['name']."' WHERE email = '" . $uname. "@bu.edu'");				mysql_query("UPDATE webData SET phone = '".$info['phone']."' WHERE email = '" . $uname. "@bu.edu'");				mysql_query("UPDATE webData SET fax = '".$info['fax']."' WHERE email = '" . $uname. "@bu.edu'");				mysql_query("UPDATE webData SET ofhours = '".$info['ofhours']."' WHERE email = '" . $uname. "@bu.edu'");				mysql_query("UPDATE webData SET jobtitle = '".$info['jobtitle']."' WHERE email = '" . $uname. "@bu.edu'");				mysql_query("UPDATE webData SET office = '".$info['office']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		// If someone is updating their biography		if (isset($_POST['savebio']) && $_POST['savebio'])		{			if ($info)			{				$info['bio'] = substr($_POST['bio'],0,1480);				mysql_query("UPDATE webData SET bio = '".$info['bio']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		// If someone is adding a class to their class list		if (isset($_POST['teach']) && $_POST['teach'])		{			if ($info)			{				$info['teaching'] = $info['teaching'].$_POST['name'].";".$_POST['link']."; - ".$_POST['desc'].";";				mysql_query("UPDATE webData SET teaching = '".$info['teaching']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		// If someone is deleting all their classes		if (isset($_POST['clearteach']) && $_POST['clearteach'])		{			if ($info)			{				$info['teaching'] = "";				mysql_query("UPDATE webData SET teaching = '' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		// If someone updated their research summary		if (isset($_POST['saveres']) && $_POST['saveres'])		{			if ($info)			{				$info['researchsum'] = substr($_POST['res'],0,1480);				mysql_query("UPDATE webData SET researchsum = '".$info['researchsum']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		//If someone updated their awards information		if (isset($_POST['saveawards']) && $_POST['saveawards'])		{			if ($info)			{				file_put_contents($uname."/awards.html",$_POST['res']);				$info['awards'] = $_POST['enable'];				mysql_query("UPDATE webData SET awards = '".$info['awards']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		// If someone updated their projects information		if (isset($_POST['saveprojects']) && $_POST['saveprojects'])		{			if ($info)			{				file_put_contents($uname."/projects.html",$_POST['res']);				$info['projects'] = $_POST['enable'];				mysql_query("UPDATE webData SET projects = '".$info['projects']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		//If someone updated their students information		if (isset($_POST['savestudents']) && $_POST['savestudents'])		{			if ($info)			{				file_put_contents($uname."/students.html",$_POST['res']);				$info['students'] = $_POST['enable'];				mysql_query("UPDATE webData SET students = '".$info['students']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}		// If someone updated their personal information		if (isset($_POST['savepersonal']) && $_POST['savepersonal'])		{			if ($info)			{				file_put_contents($uname."/personal.html",$_POST['res']);				$info['personal'] = $_POST['enable'];				mysql_query("UPDATE webData SET personal = '".$info['personal']."' WHERE email = '" . $uname. "@bu.edu'");			}			else			{				die('Internal Server Error : Code 101');			}		}	}}?>    <script language = "Javascript">    /**     * DHTML textbox character counter script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)     */    maxL=1500;    var bName = navigator.appName;    function taLimit(taObj)    {		if (taObj.value.length==maxL)		{			return false;		}		return true;    }    function taCount(taObj,Cnt)    {		objCnt=createObject(Cnt);		objVal=taObj.value;		if (objVal.length>maxL)		{			objVal=objVal.substring(0,maxL);		}		if (objCnt)		{			if(bName == "Netscape")			{				objCnt.textContent=maxL-objVal.length;			}			else			{				objCnt.innerText=maxL-objVal.length;			}		}		return true;    }    function createObject(objId)    {		if (document.getElementById)		{			return document.getElementById(objId);		}		else if (document.layers)		{			return eval("document." + objId);		}		else if (document.all)		{			return eval("document.all." + objId);		}		else		{			return eval("document." + objId);		}    }    </script>    <div style="position: relative; <?php if (strstr($_SERVER['HTTP_USER_AGENT'],"Mozilla") != "") echo 'top:-15px;'; ?> width: 945px; height:auto; background: url('images/Body.jpg'); text-align:left;">        <div style="position: relative; left:30px; width:880px; top:10px; font-size: 11px; font-family:Tahoma;">            <?php            if ($approved)            {                if ($_SESSION['usertype'] == 1)                {            ?>            <span style="font-size: 16px;"><b>Website Management Panel</b></span>            <hr width='880px'>            <span style="text-align:right;">Welcome, <?php echo $_SESSION['name']; ?>!</span>            <hr width='880px'>            <h3>Website Settings:</h3>            <form action="" method="POST">                Online : <input type="radio" name="isonline" value="1" <?php if ($info && $info['isonline']) echo 'checked' ?>/>                &nbsp;&nbsp;Offline:<input type="radio" name="isonline" value="0" <?php if (!$info || !$info['isonline']) echo 'checked' ?> />                <br><br><input type="submit" value="Apply" name="websettings" />            </form>            <hr width='880px'>            <h3>Picture:</h3>            <table style="border-style:solid; border-width:1px">                <tr>                    <td>                        <img src="<?php echo $uname; ?>/picture.jpg" width="60px" height="90px"/>                    </td>                </tr>            </table>            <br>            Your picture should be approximately 230x350 pixels.<br><br>            <form enctype="multipart/form-data" action="" method="POST">            <input type="hidden" name="MAX_FILE_SIZE" value="100000" />            Choose a picture to upload: <input name="uploadedfile" type="file" size="64" /><br /><br>            <input type="submit" value="Upload Picture" name="upload"/>            </form>            <hr width='880px'>            <h3>General Information:</h3>            Tip : Use HTML tags for special formatting.<br><br>            <form action="" method="POST">                Display Name : <input type="text" name="name" value="<?php echo $info['name'] ?>" size="40" /><br><br>                Job Title : <input type="text" name="job" value="<?php echo $info['jobtitle'] ?>" size="40" /><br><br>                Office Address : <input type="text" name="address" value="<?php echo $info['office'] ?>" size="40" /><br><br>                Tel : <input type="text" name="tel" value="<?php echo $info['phone'] ?>" size="40" /><br><br>                Fax : <input type="text" name="fax" value="<?php echo $info['fax'] ?>" size="40" /><br><br>                Office hours : <input type="text" name="ohrs" value="<?php echo $info['ofhours'] ?>" size="40" />                &nbsp;&nbsp;(Use this format [DAY TT:TT - TT:TT])                <br><br>                <input type="submit" value="Save" name="geninfo" />            </form>            <hr width='880px'>            <h3>Short Biography:</h3>            Tip : Use HTML tags for special formatting.<br><br>            <form action="" method="POST">                <textarea onKeyPress="return taLimit(this)" onKeyUp="return taCount(this,'myCounter')" name="bio" rows=7 wrap="physical" cols=100><?php echo $info['bio'];?></textarea>                <br><br>                You have <B><SPAN id=myCounter>1500</SPAN></B> characters remaining.</font><br><br>                <input type="submit" value="Save" name="savebio" />            </form>            <hr width='880px'>            <h3>Teaching:</h3>            <?php                // Print out course list                $clist = $info['teaching'];                while ($clist != "")                {                    $title = substr($clist,0,strpos($clist,";"));                    $clist = substr($clist,strpos($clist,";")+1);                    $link = substr($clist,0,strpos($clist,";"));                    $clist = substr($clist,strpos($clist,";")+1);                    $desc = substr($clist,0,strpos($clist,";"));                    $clist = substr($clist,strpos($clist,";")+1);                    echo $title.$desc."<br><br>";                }            ?>            <form action="" method="POST">                Course Name : <input type="text" name="name" value="" size="100" /><br><br>                Course Website Link : <input type="text" name="link" value="" size="100" /><br><br>                Short Description : <input type="text" name="desc" value="" size="100" /><br><br>                <input type="submit" value="Add New" name="teach" />                &nbsp;&nbsp;<input type="submit" value="Delete All" name="clearteach" />            </form>            <hr width='880px'>            <h3>Research:</h3>            Research Summary : <br>            ( Tip : Use HTML tags for special formatting. )<br><br>            <form action="" method="POST">                <textarea onKeyPress="return taLimit(this)" onKeyUp="return taCount(this,'myCounter2')" name="res" rows=7 wrap="physical" cols=100><?php echo $info['researchsum'];?></textarea>                <br><br>                You have <B><SPAN id=myCounter2>1500</SPAN></B> characters remaining.</font><br><br>                <input type="submit" value="Save" name="saveres" />            </form>            <hr width='880px'>            <h3>Awards:</h3>            ( Tip : Use HTML tags for special formatting. )<br><br>            <form action="" method="POST">                <textarea name="res" rows=7 wrap="physical" cols=100><?php echo file_get_contents($uname."/awards.html");?></textarea>                <br><br>Enabled : <input type="checkbox" name="enable" value="1" <?php if ($info['awards'] == "1") echo "checked"; ?> />                <br><br>                <input type="submit" value="Save" name="saveawards" />            </form>            <hr width='880px'>            <h3>Projects:</h3>            ( Tip : Use HTML tags for special formatting. )<br><br>            <form action="" method="POST">                <textarea name="res" rows=7 wrap="physical" cols=100><?php echo file_get_contents($uname."/projects.html");?></textarea>                <br><br>Enabled : <input type="checkbox" name="enable" value="1" <?php if ($info['projects'] == "1") echo "checked"; ?>  />                <br><br>                <input type="submit" value="Save" name="saveprojects" />            </form>            <hr width='880px'>            <h3>Students:</h3>            ( Tip : Use HTML tags for special formatting. )<br><br>            <form action="" method="POST">                <textarea name="res" rows=7 wrap="physical" cols=100><?php echo file_get_contents($uname."/students.html");?></textarea>                <br><br>Enabled : <input type="checkbox" name="enable" value="1"  <?php if ($info['students'] == "1") echo "checked"; ?> />                <br><br>                <input type="submit" value="Save" name="savestudents" />            </form>            <hr width='880px'>            <h3>Personal:</h3>            ( Tip : Use HTML tags for special formatting. )<br><br>            <form action="" method="POST">                <textarea name="res" rows=7 wrap="physical" cols=100><?php echo file_get_contents($uname."/personal.html");?></textarea>                <br><br>Enabled : <input type="checkbox" name="enable" value="1"  <?php if ($info['personal'] == "1") echo "checked"; ?> />                <br><br>                <input type="submit" value="Save" name="savepersonal" />            </form>            <?php                }                else if ($_SESSION['usertype'] == 2) //If the user is level 2, an admin...                {            ?>            <span style="font-size: 16px;"><b>User Administration Panel</b></span><br><br>            <table border="2px"style="font-size: 14px;" cellpadding="5px">                <tr><th>User Email</th><th>Created</th><th>Activated</th></tr>            <?php                $info = mysql_fetch_array( $check );                while ($info)                {                    $created = mysql_query("SELECT * FROM nLogin WHERE email ='".$info['email']."'") or die(mysql_error());                    echo "<tr>";                    echo "<td>".$info['email']."</td>";                    $test = mysql_fetch_array( $created );                    echo "<td><form action='' method='POST'><input type='hidden' name='user' value='".$info['email']."' readonly='readonly' /><input type='submit' value='Delete' name='deluser' ";                    if ($test['isactive'] == 2) echo "disabled";                    echo "/></form></td>";                    echo "<td>";                    if ($test && $test['isactive'] != 2)                    {                        if ($info['isApproved'] == 0)                        {                            echo "<form action='' method='POST'><input type='hidden' name='user' value=".$info['email']." readonly='readonly' /><input type='submit' value='Approve' name='appuser' /></form>";                            if ($info['hash']) echo '(Email sent)';                        }                        else                        {                            echo "<form action='' method='POST'><input type='hidden' name='user' value=".$info['email']." readonly='readonly' /><input type='submit' value='Deauthorize' name='deauthorize' /></form>";                        }                    }                    else                    {                        echo "<form><input type='submit' value='Approve' name='appuser' disabled/></form>";                    }                    echo "<td>";                    echo "</tr>";                    $info = mysql_fetch_array( $check );                }            ?>                <tr><td colspan="3"><form action="" method="POST"><input type="text" name="email" value="" size="20" /><input type="submit" value="Add" name="adduser" /></form></td></tr>            </table>            <?php                }            }            else            {                echo "<h3>Your account is not approved by the administrator yet.</h3><br><br>.";            }            ?>            <br><br>.        </div>    </div></center>