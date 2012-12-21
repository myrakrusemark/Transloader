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

Place files in htdocs or www root.

Create a folder anywhere on the computer. Put two folders: "tokens" and "uploads" inside this folder. Preferably somewhere other than your root folder so they are hidden from snoopers.

Create an app at developer.dropbox.com.
  Add app-key and app-secret strings to index.php

Add app key and secret to index.php.
Add your htdocs or www root path where index.php asks for it.
Add your tokens/uploads folders path where index.php asks for it.
Add your email server details to emailer.pl. This currently only uses Gmail services but can be modified for others.

Contact alnino2005 at github with any issues