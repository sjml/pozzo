#!/usr/bin/env php

<?php

require_once __DIR__ . "/../app/db.php";

DB::Init();
register_shutdown_function(["DB", "Cleanup"]);

echo "Username? > ";
$userName = trim(fgets(STDIN));
echo "Password? > ";
system('stty -echo');
$pw1 = trim(fgets(STDIN));
system('stty echo');
echo "\nConfirm Password? > ";
system('stty -echo');
$pw2 = trim(fgets(STDIN));
system('stty echo');

if ($pw1 != $pw2) {
    echo "\nERROR: Passwords don't match. :( \n";
    exit(1);
}

$result = DB::CreateUser($userName, $pw1);
if ($result == false) {
    echo "\nERROR: Could not create user. :( \n";
}
echo "\nUser " . $userName . " created with id: " . $result . ".\n";


