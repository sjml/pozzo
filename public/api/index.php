<?php

require_once __DIR__ . "/../../app/db.php";
DB::Init();
register_shutdown_function(["DB", "Cleanup"]);

$request = $_SERVER["REQUEST_URI"];

switch ($request) {
    case "/api/":
        require __DIR__ . "/../../app/actions/index.php";
        break;
    case "/api/list":
        require __DIR__ . "/../../app/actions/list.php";
        break;
    case "/api/reset":
        require __DIR__ . "/../../app/actions/reset.php";
        break;
    case "/api/upload":
        require __DIR__ . "/../../app/actions/upload.php";
        break;
    case "/api/testImport":
        require __DIR__ . "/../../app/actions/testImport.php";
        break;
    default:
        require __DIR__ . "/../../app/actions/404.php";
}
