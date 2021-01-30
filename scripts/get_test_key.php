<?php

require_once __DIR__ . "/test_credentials.php";
require_once __DIR__ . "/../app/db.php";

DB::Init();
register_shutdown_function(["DB", "Cleanup"]);

$result = DB::GetUser($testUserName);
$jwt = generateJWT($result, DB::GetConfig("app_key"), -1, 1000);

echo trim($jwt);

