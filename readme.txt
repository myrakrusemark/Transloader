Transloader
===========

A PHP, Perl, BASH dropbox uploader using Internet URLs

Prerequisites:
Perl
  IO::Socket::SSL 1.78 - Script will error without downgrading to this package
  WebService::Dropbox
  Emailer::Send
  Emailer::Send::Gmail
  Emailer::Simple::Creator
PHP
Web Server
BASH interpreter

Place files in server folder

Create 'upload' and 'tokens' folder

Create an app at developer.dropbox.com.
  Add app-key and app-secret strings to index.php

Contact alnino2005 at github with any issues