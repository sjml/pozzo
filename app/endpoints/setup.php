<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";

if (DB::GetConfig("site_title") != false) {
    header("Content-Type: application/json");
    http_response_code(403);
    echo json_encode(["message" => "Site already set up."]);
    return;
}

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input["siteTitle"])) {
    output(["message" => "Missing parameter 'siteTitle'"], 400);
    return;
}
if (!isset($input["userName"])) {
    output(["message" => "Missing parameter 'userName'"], 400);
    return;
}
if (!isset($input["password"])) {
    output(["message" => "Missing parameter 'password'"], 400);
    return;
}

DB::SetConfig("site_title", $input["siteTitle"], "string");
DB::SetConfig("promo", 0, "integer");
DB::SetConfig("default_aspect", 1.3333, "float");
$user = DB::CreateUser($input["userName"], $input["password"]);
if ($user == false) {
    // @codeCoverageIgnoreStart
    // Here to catch fundamental setup issues -- don't have write permissions,
    //   PHP is broken, etc. Only for first-run user who might not know what
    //   they're doing. (Arguably this message would not help them much, either.)
    output(["message" => "Something went wrong. :("], 500);
    return;
    // @codeCoverageIgnoreEnd
}

$jwt = Auth::GenerateJWT(
    $user,
    DB::GetConfig("app_key"),
    0,
    DB::GetConfig("jwt_expiration"),
);

output(["message" => "Setup complete!", "key" => $jwt]);
