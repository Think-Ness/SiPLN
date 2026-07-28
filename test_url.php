<?php
require 'vendor/autoload.php';

// Wait, booting Yii3 in a standalone script is complex. Let's just create a route that dumps it, or use curl to login and fetch the page!
// I'll simulate a login.
$ch = curl_init("http://localhost/webapp/public/api/login");
curl_setopt($ch, CURLOPT_POST, 1);
// Wait, we need a valid firebase token to login.
// Is there a simpler way?
