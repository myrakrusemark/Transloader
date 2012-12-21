 use WebService::Dropbox;
 use LWP::Simple;  
 #use strict;
 #use warnings;

my $url = 			$ARGV[0];
my $access_token = 	$ARGV[1];
my $access_secret = $ARGV[2];
my $app_token = 	$ARGV[3];
my $app_secret = 	$ARGV[4];
my $app_path = 		$ARGV[5];
my $save_path =		$ARGV[6];

my @filenametmp =	split('\/', $url);
	my $file_name = 	@filenametmp[scalar(@filenametmp)-1];
	
#Dropbox OAuth
my $dropbox = WebService::Dropbox->new({
    key => $app_token, # App Key
    secret => $app_secret # App Secret
	});	
	
if (!$access_token or !$access_secret){
	$dropbox->auth or die $dropbox->error;
	
	print "One of these is wrong: <br />\n".
		"App token: ".$app_token."<br />\n".
		"App secret: ".$app_secret."<br />\n".
		"User token: ".$access_token."<br />\n".
		"User secret: ".$access_secret."<br />\n";
		
} else {
	$dropbox->access_token($access_token);
	$dropbox->access_secret($access_secret);
}

#Welcome message
my $info = $dropbox->account_info or die $dropbox->error;
print "Welcome, ".$info->{"display_name"}." (".$info->{"email"}.")\n";
	
#Fetch file and save to upload folder
my $status = getstore($url, $save_path."uploads/".$file_name);
 	sleep(2);

#Upload
if ($file_name || $filename != '')
{
	#Replace absolute paths with cygwin style paths
	my $command = 'sh "'.$app_path.'dropbox_uploader.sh" upload '.$save_path.'uploads/'.$file_name.' '.$file_name.' '.$app_token.' '.$app_secret.' '.$access_token.' '.$access_secret;
	
	print($command);
		
	my $retval = system($command);
			
	if($retval != 0) {
		die "Dropbox.sh:".$retval." File didn't upload\n";
		exit 4;
	}
	#Email recipient	
	$command = 'perl "'.$app_path.'emailer.pl" '.$info->{"email"}.' "Your Dropbox Upload" "Congratulations, '.$info->{"display_name"}.'! Your upload of '.$file_name.' is finished."';		
		
	$retval = system($command);
print($command);

	#Implementing	
	#	if($retval != 0)
	#	{
	#		die "Emailer.pl:".$retval." Couldn't send email!";
	#		exit 3;
	#	}
	
} else {
	print("Error: No upload\\".$file_name);
	exit 5;	
}