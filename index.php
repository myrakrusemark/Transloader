<?php
error_reporting(E_ALL);
require_once("DropboxClient.php");

// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
$app_path = "C:\Program Files (x86)\Ampps\www\dropdemo\\";
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
	
// if there is no upload, show the form
if(empty($_POST["fileURL"])) {
?>

<form enctype="multipart/form-data" method="POST" action="">
File URL:<input type="text" name="fileURL" id="name" value="Enter URL" onfocus="if (this.value=='Enter URL') this.value='';"/>
<input type="submit" value="Transload!" title="Search" >
</form>
<a id="unlink" href="?deauth=yes">Unlink</a>

<?php 

} else { 
	$file_URL = $_POST["fileURL"];
	$filename_tmp = explode("/", $file_URL);
	$file_name = $filename_tmp[sizeof($filename_tmp)-1];


	$command = '"'.$app_path.'drop.pl" '.$file_URL." ".$user_token." ".$user_secret." ".$app_token." ".$app_secret." \"".$app_path."\"";
	
print($command);	
	if(strpos($file_name, "php?") !== false ||
		strpos($file_name, ".") == false){
	$retval = 2;
	} else{
		$retval = exec($command);
	}
	
	switch ($retval){
		case 2:
			echo $retval.": Bad or malformed URL<br />";
			break;
		case 3:
			echo $retval.": Error sending email<br />";
			break;
		case 4:
			echo $retval.": Error uploading to Dropbox<br />";
			break;
		case 5:
			echo $retval.": Error downloading to server!<br />";
			break;
		default:
			echo "Success! Please wait for an email.<br />";
			break;		
	}
	
	echo '<a href="">Upload a new file</a>';
	echo "</pre>";
}



//Dropbox-API Functions
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
		die("<a href='$auth_url'>Click here</a> to log in to Dropbox.");
	}
}

?>