<?php

//session_destroy();
session_start();
$_SESSION['OAUTH_ACCESS_TOKEN']['https://accounts.google.com/o/oauth2/token']['expiry'] = gmstrftime('%Y-%m-%d %H:%M:%S', time());
print "hoge";
//$_SESSION = array();

?>
