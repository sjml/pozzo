<?php

require_once __DIR__ . "/../../app/router.php";
require_once __DIR__ . "/../../app/db.php";
DB::Init();
register_shutdown_function(["DB", "Cleanup"]);

$POZZO_REQUEST = $_SERVER["REQUEST_URI"];
$POZZO_REQUEST = preg_replace("/^\/api/", "", $POZZO_REQUEST);
if ($POZZO_REQUEST == "/") {
    $POZZO_REQUEST = "/index";
}

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
$router->AddHandler("/testImport", [
    "require",
    __DIR__ . "/../../app/endpoints/testImport.php",
]);

$router->Route();
