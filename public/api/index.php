<?php

require_once __DIR__ . "/../../app/auth.php";
require_once __DIR__ . "/../../app/router.php";

require_once __DIR__ . "/../../app/db.php";
DB::Init();
register_shutdown_function(["DB", "Cleanup"]);

// if running the dev server, have to send an error header first and then correct
//   once we know it's ok
if ($_SERVER["SERVER_NAME"] == "0.0.0.0") {
    register_shutdown_function(function () {
        $error = error_get_last();
        switch ($error['type'] ?? 0) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_RECOVERABLE_ERROR:
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => "server-side error"]);
            break;
        }
    });
    http_response_code(500);
}

$_REQUEST["POZZO_REQUEST"] = $_SERVER["REQUEST_URI"];
$_REQUEST["POZZO_REQUEST"] = preg_replace("/^\/api/", "", $_REQUEST["POZZO_REQUEST"]);
if ($_REQUEST["POZZO_REQUEST"] == "/") {
    $_REQUEST["POZZO_REQUEST"] = "/index";
}

$_REQUEST["POZZO_AUTH"] = 0;
$_REQUEST["POZZO_AUTH"] = Auth::Validate();

$router = new Router();

$router->AddHandler("/index", [
    "require",
    __DIR__ . "/../../app/endpoints/index.php",
]);
$router->AddHandler("/login", [
    "require",
    __DIR__ . "/../../app/endpoints/login.php",
]);
$router->AddHandler("/album", [
    "require",
    __DIR__ . "/../../app/endpoints/album.php",
]);
$router->AddHandler("/photo", [
    "require",
    __DIR__ . "/../../app/endpoints/photo.php",
]);
$router->AddHandler("/list", [
    "require",
    __DIR__ . "/../../app/endpoints/list.php",
]);
$router->AddHandler("/reset", [
    "require",
    __DIR__ . "/../../app/endpoints/reset.php",
]);
$router->AddHandler("/upload", [
    "require",
    __DIR__ . "/../../app/endpoints/upload.php",
]);

$router->Route();
