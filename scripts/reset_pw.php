#!/usr/bin/env php

<?php
// There is no way to reset a user password through the API.
// This is by design, because it's one less thing to worry about
//   testing and security-wise. This script can be used to directly
//   invoke the PHP calls to reset a user password, though, in case
//   you need to do that.

require_once __DIR__ . "/../vendor/autoload.php";

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../app/db.php";

if (PHP_VERSION_ID < 70400) {
    echo "Requires PHP >= 7.4";
    exit(1);
}

DB::Init();
register_shutdown_function(["DB", "Cleanup"]);

echo "Username? > ";
$userName = trim(fgets(STDIN));
echo "New Password? > ";
system("stty -echo");
$pw1 = trim(fgets(STDIN));
system("stty echo");
echo "\nConfirm Password? > ";
system("stty -echo");
$pw2 = trim(fgets(STDIN));
system("stty echo");

if ($pw1 != $pw2) {
    echo "\nERROR: Passwords don't match. :( \n";
    exit(1);
}

$result = DB::UpdateUserPassword($userName, $pw1);
if ($result == false) {
    echo "\nERROR: Could not update user. :( \n";
    exit(1);
}
echo "\nUser " . $userName . " password updated.\n";

