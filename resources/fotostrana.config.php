<?php

define('FOTOSTRANA_APPID', 'sdkdemo');
define('FOTOSTRANA_CLIENTKEY', 'a84aedc74dda60a3cf35a96683ae0947');
define('FOTOSTRANA_SERVERKEY', 'c95e03377ca085d6f82c9b39fbf9336e');

define('FOTOSTRANA_URL', 'http://fs98.vs58.net');
define('FOTOSTRANA_API_BASEURL', 'http://fs98.vs58.net/apifs.php');
define('FOTOSTRANA_OAUTH_CALLBACK', 'http://'.$_SERVER['HTTP_HOST'].'/sdk/callback-example.php');
define('FOTOSTRANA_REQUIRED_PERMISSIONS', 'basic,friends');
define('FOTOSTRANA_DEBUG', 0);
define('FOTOSTRANA_REQUESTS_CACHE_TIMEOUT', 5);

define('FOTOSTRANA_SESSION_KEY', $_REQUEST['sessionKey']);
define('FOTOSTRANA_VIEWER_ID', $_REQUEST['viewerId']);

define('FOTOSTRANA_EXCHANGE', 1);

// битовые маски для appSettings

define('FOTOSTRANA_MASK_DEFAULT', 1);
define('FOTOSTRANA_MASK_USERWALL', 2);
define('FOTOSTRANA_MASK_USERCOMMUNITIES', 4);
define('FOTOSTRANA_MASK_USERFORUM', 8);
define('FOTOSTRANA_MASK_USERINVITE', 16);
define('FOTOSTRANA_MASK_USERNOTIFY', 32);
define('FOTOSTRANA_MASK_SILENT_BILLING', 64);
define('FOTOSTRANA_MASK_USERPHOTO', 128);
define('FOTOSTRANA_MASK_USEREMAIL', 512);

?>