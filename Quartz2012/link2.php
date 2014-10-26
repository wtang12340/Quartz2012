<?php

//Page that shows to allow admin to send email using email client 

session_start();

echo $_SESSION['mailinfo'];
echo "<br>";
echo "<a href='index.php'>Return to Admin panel</a>";

?>