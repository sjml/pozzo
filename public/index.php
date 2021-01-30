<?php

$request = $_SERVER["REQUEST_URI"];

switch ($request) {
    case "/":
        require __DIR__ . "/../app/actions/index.php";
        break;
    case "/list":
        require __DIR__ . "/../app/actions/list.php";
        break;
    case "/reset":
        require __DIR__ . "/../app/actions/reset.php";
        break;
    case "/upload":
        require __DIR__ . "/../app/actions/upload.php";
        break;
    case "/testImport":
        require __DIR__ . "/../app/actions/testImport.php";
        break;
    default:
        require __DIR__ . "/../app/actions/404.php";
}
