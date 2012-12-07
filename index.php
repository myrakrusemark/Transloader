<html>

<head>
<link rel="stylesheet" type="text/css" href="style.css">
<link href='http://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Nixie+One' rel='stylesheet' type='text/css'>
<meta name="viewport" content="initial-scale=1.0">
</head>

<body>
<h1>The Transloader</h1>

<div class="box">
<center><p>The NEW Transloader is a nifty way to save your information to your Dropbox!</p>
<ul>
<li>Your Dropbox is safe! Transloader uploads to its own folder. It can't even see the rest.</li>
<li>No flash drive? File too big? Just use this app! Your file will be waiting for you.</li></center>


<?php
error_reporting(E_ALL);
require_once("DropboxClient.php");
require_once 'login.php';

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if(!$db_server) die("Unable to snog MySQL: " . mysql_error());
mysql_select_db($db_database, $db_server)
  or die("Unabe to select smorgasborg: " . mysql_error());
	
$mysql = new mysqli($db_hostname, $db_username, $db_password, 'drop');

// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:

	
$app_token = "gi8ihcnep3sxr13";
$app_secret = "1vrjqtxthgvl0oo";
$dropbox = new DropboxClient(array(
	'app_key' => $app_token, 
	'app_secret' => $app_secret,
	'app_full_access' => true,
),'en');


	
$token_file = "tokens/".str_replace(".", "-", $_SERVER['REMOTE_ADDR']).".token";
	if(isset($_GET['deauth']) && $_GET['deauth'] == 'yes') {
			unlink($token_file);
	}
handle_dropbox_auth($dropbox); // see below



$tokenarray = explode('"', file_get_contents($token_file));
$user_token = $tokenarray[3];
$user_secret = $tokenarray[7];

	#echo $user_token.'<br />';
	#echo $user_secret.'<br />';
	

	
// if there is no upload, show the form
if(empty($_POST["fileURL"])) {
?>
<center><p>Just enter the URL of the file you wish to download in the field below.</p></center>
<div id="stylized" class="myform">
<form enctype="multipart/form-data" method="POST" action="">
<input type="text" name="fileURL" id="name" value="Enter URL" onfocus="if (this.value=='Enter URL') this.value='';"/>
<input type="submit" value="Transload" title="Search" >
</form>
<a id="unlink" href="?deauth=yes">Unlink</a>
</div>
</div>

<?php 

} else { 
	$file_URL = $_POST["fileURL"];
	$ufn = str_replace(".", "-", $_SERVER['REMOTE_ADDR']).'@'.time();
	 
	 $sql = 'INSERT INTO upload VALUES(\''.$ufn.'\', \''.$file_URL.'\')';
	 mysqli_query($mysql, $sql) 
		or die(mysqli_error($mysql));

	$command = '"C:\Program Files (x86)\Ampps\www\drop\drop.pl" '.$file_URL." ".$user_token." ".$user_secret." ".$app_token." ".$app_secret." > NUL";

	#echo $command.'<br />';
	
 	ob_start();
	$retval = exec($command);
	ob_end_clean();
		
		switch ($retval){
		case 3:
			echo $retval.": Couldn't send email!<br />";
			break;
		case 4:
			echo $retval.": File couldn't upload!<br />";
			break;
		case 5:
			echo $retval.": File couldn't download to server!<br />";
			break;
		default:
			echo "<b>Finished! Please wait for an email.</b><br /><br />";
			break;		
		}
	
	echo '<a href="/drop/">Upload a new file</a>';
	echo "</pre>";
}









// ================================================================================
// store_token, load_token, delete_token are SAMPLE functions! please replace with your own!
function store_token($token_file, $name)
{
	file_put_contents("tokens/$name.token", serialize($token_file));

}

function load_token($name)
{
	if(!file_exists("tokens/$name.token")) return null;
	return @unserialize(@file_get_contents("tokens/$name.token"));

}

function delete_token($name)
{
	@unlink("tokens/$name.token");
}
// ================================================================================

function handle_dropbox_auth($dropbox)
{
	// first try to load existing access token
	$access_token = load_token(str_replace(".", "-", $_SERVER['REMOTE_ADDR']));
	if(!empty($access_token)) {
		$dropbox->SetAccessToken($access_token);
	}
	elseif(!empty($_GET['auth_callback'])) // are we coming from dropbox's auth page?
	{
		// then load our previosly created request token
		$request_token = load_token($_GET['oauth_token']);
		if(empty($request_token)) die('Request token not found!');
		
		// get & store access token, the request token is not needed anymore
		$access_token = $dropbox->GetAccessToken($request_token);	
		store_token($access_token, str_replace(".", "-", $_SERVER['REMOTE_ADDR']));
		delete_token($_GET['oauth_token']);
	}

	// checks if access token is required
	if(!$dropbox->IsAuthorized())
	{
		// redirect user to dropbox auth page
		$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
		$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
		$request_token = $dropbox->GetRequestToken();
		store_token($request_token, $request_token['t']);
		die("Just <a href='$auth_url'>click here</a> to log in to Dropbox.");
	}
}
?>
<div class="bot">
A production by Alan Helton, 2012.<a id="dropbox" href="http://www.dropbox.com"><img id="dblogo" src="dblogo.png" width=24 height=24></a>
</div>
</body>