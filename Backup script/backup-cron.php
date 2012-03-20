<?php
#################################################
## Backup CRONJOB API v 1.0                    ##
## HTML24									   ##
## Bo Møller								   ##
## bm@html24.dk								   ##
#################################################

// READ THIS FIRST!! :-)
// This script creates a backupfile in a folder called backup/backupfile.sql
// The file is called the name of the MySQL database + a timestamp + .sql.
// Create the folder "backup" in the same dir as this script.
// All backups will be stored there, so make sure that dir is not accessible from outside! Otherwise people will be able to download the database-dump.
// The script will automatically send an e-mail with the newly created backup file.

# ---------------- Settings ---------------- #

$db_user = "";
$db_host = "";
$db_password = "";
$db_name = "";
$email = ""; // Who should the e-mail with the backup be sent to?
$from_email = "";
$path = "/usr/bin/mysqldump"; // Don't change this unless you know what you're doing - this is a default path for mysqldump, which is normally installed on an Apache server.


// Global vars
$dumpfileName = "";

function backupSQL(){

	global $db_user, $db_host, $db_password, $db_name, $path;

	echo "<br/>Running SQL backup<br/>";
	echo "-----------------<br/>";
	
	
	$dbhost   = $db_host;
	$dbuser   = $db_user;
	$dbpwd    = $db_password;
	$dbname   = $db_name;
	$dumpfile = "backup/" . $dbname . "_" . date("Y-m-d_H-i-s") . ".sql";

	global $dumpfileName;
	$dumpfileName  = $dumpfile;
	
	passthru($path . " --opt --host=$dbhost --user=$dbuser --password=$dbpwd $dbname > $dumpfile");

	echo "$dumpfile "; passthru("tail -1 $dumpfile");
	
	
}
		
function mail_attachment($to, $subject, $message, $from, $file) {

	echo "<br/><br/>Sending e-mail with backup<br/>";
	echo "-----------------<br/>";

	// $file should include path and filename
	$filename = basename($file);
	$file_size = filesize($file);
	$content = chunk_split(base64_encode(file_get_contents($file))); 
	$uid = md5(uniqid(time()));
	$from = str_replace(array("\r", "\n"), '', $from); // to prevent email injection
	$header = "From: ".$from."\r\n"
	  ."MIME-Version: 1.0\r\n"
	  ."Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n"
	  ."This is a multi-part message in MIME format.\r\n" 
	  ."--".$uid."\r\n"
	  ."Content-type:text/plain; charset=iso-8859-1\r\n"
	  ."Content-Transfer-Encoding: 7bit\r\n\r\n"
	  .$message."\r\n\r\n"
	  ."--".$uid."\r\n"
	  ."Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"
	  ."Content-Transfer-Encoding: base64\r\n"
	  ."Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n"
	  .$content."\r\n\r\n"
	  ."--".$uid."--"; 
	  
	  echo "E-mail sent!";
	  
	return mail($to, $subject, "", $header);
}
# ------ CODE EXEC ------ #

backupSQL();
mail_attachment($email ,"Automatic backup - " . $dumpfileName,"Backup attached (" . $dumpfileName . ")",$from_email,$dumpfileName);
?>
