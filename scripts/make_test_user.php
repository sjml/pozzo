#!/usr/bin/env php
<?php
require_once __DIR__ . "/test_credentials.php";
require_once __DIR__ . "/../app/db.php";

DB::Init();
register_shutdown_function(["DB", "Cleanup"]);

$result = DB::CreateUser($testUserName, $testUserPW);
if ($result == false) {
    echo "\nERROR: Could not create user. :( \n";
    exit(1);
}

require __DIR__ . "/get_test_key.php";

