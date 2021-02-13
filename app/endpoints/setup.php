<?php

require_once __DIR__ . "/../db.php";

if (DB::GetConfig("site_title") != false) {
    header("Content-Type: application/json");
    http_response_code(403);
    echo json_encode(["message" => "Site already set up."]);
    die();
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
$user = DB::CreateUser($input["userName"], $input["password"]);
if ($user == false) {
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode(["message" => "Something went wrong. :("]);
    die();
}

header("Content-Type: application/json");
http_response_code(200);
echo json_encode(["message" => "Setup complete!"]);
