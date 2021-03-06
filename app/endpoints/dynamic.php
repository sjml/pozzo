<?php

require_once __DIR__ . "/../db.php";

$_REQUEST["POZZO_REQUEST"] = preg_replace(
    "/^\/dynamic/",
    "",
    $_REQUEST["POZZO_REQUEST"],
);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/all", ["getAllPhotos"]);
$router->AddHandler("/unsorted", ["getUnsortedPhotos"]);

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function getAllPhotos() {
    if (DB::GetConfig("dynamic_public") == 0 && $_REQUEST["POZZO_AUTH"] <= 0) {
        output(["message" => "Unauthorized"], 401);
        return;
    }
    $photos = DB::GetAllPhotos();
    output($photos);
}

function getUnsortedPhotos() {
    if (DB::GetConfig("dynamic_public") == 0 && $_REQUEST["POZZO_AUTH"] <= 0) {
        output(["message" => "Unauthorized"], 401);
        return;
    }
    $photos = DB::GetUnsortedPhotos();
    output($photos);
}
