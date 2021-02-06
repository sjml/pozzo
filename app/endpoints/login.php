<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";

$_REQUEST["POZZO_REQUEST"] = preg_replace("/^\/login/", "", $_REQUEST["POZZO_REQUEST"]);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/check", ["checkLogin"], true);
$router->AddHandler("/logout", ["logout"]);
$router->AddHandler("/$", ["login"]);

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function checkLogin() {
    // only gets called if auth check passed
    $timeDelayToValidity = 3;
    global $router;
    $decoded = Auth::DecodeJWT($router->GetJWT(), DB::GetConfig("app_key"));
    $newToken = Auth::GenerateJWT(
        $decoded->data,
        DB::GetConfig("app_key"),
        $timeDelayToValidity,
        DB::GetConfig("jwt_expiration"),
    );

    output([
        "message" => "Login valid",
        "newToken" => $newToken,
        "validIn" => $timeDelayToValidity,
    ]);
}

function login() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["userName"])) {
        output(["message" => "Missing parameter 'userName'"], 400);
        return;
    }
    if (!isset($input["password"])) {
        output(["message" => "Missing parameter 'password'"], 400);
        return;
    }

    $result = DB::GetUser($input["userName"], true);
    if ($result == null) {
        output(["message" => "No such user"], 404);
        return;
    }

    if (!Auth::VerifyPassword($input["password"], $result["password"])) {
        output(["message" => "Login failed"], 403);
        return;
    }

    unset($result["password"]);

    $jwt = Auth::GenerateJWT(
        $result,
        DB::GetConfig("app_key"),
        3,
        DB::GetConfig("jwt_expiration"),
    );

    output(["message" => "Login successful", "token" => $jwt]);
}
