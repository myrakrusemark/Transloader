   #!/usr/bin/perl -w
  use Email::Send;
  use Email::Send::Gmail;
  use Email::Simple::Creator;
 
  $recipient =   $ARGV[0];
  $subject = 	$ARGV[1];
  $message = 	$ARGV[2];

 
		  my $email = Email::Simple->create(
			  header => [
				  From    => '',
				  To      => $recipient,
				  Subject => $subject,
			  ],
			  body => $message,
		  );

		  my $sender = Email::Send->new(
			  {   mailer      => 'Gmail',
				  mailer_args => [
					  username => '',
					  password => '',
				  ]
			  }
			  );
	
			eval { $sender->send($email) }
