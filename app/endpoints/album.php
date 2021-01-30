<?php

require_once __DIR__ . "/../db.php";

$POZZO_REQUEST = preg_replace("/^\/album/", "", $POZZO_REQUEST);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

// $router->AddHandler("/index", ["albumIndex"]); // give list of albums
$router->AddHandler("/new", ["newAlbum"]);
$router->AddHandler("/view", ["viewAlbum"]);
$router->AddHandler("/remove", ["removePhoto"]);
$router->AddHandler("/delete", ["deleteAlbum"]);
// $router->AddHandler("/edit", ["editAlbum"]); // change album's title or description

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

// create new album with given title, return index
function newAlbum() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["title"])) {
        output(["message" => "Missing 'title'"], 400);
        return;
    }

    $result = DB::CreateAlbum($input["title"]);
    if ($result == -1) {
        output(["message" => "Could not create album: Duplicate title"], 400);
        return;
    }
    if ($result == -2) {
        output(["message" => "Could not create album: Invalid title"], 400);
        return;
    }
    output(["albumID" => $result]);
}

function viewAlbum() {
    global $POZZO_REQUEST;
    $identifier = substr($POZZO_REQUEST, 1);
    $album = DB::FindAlbum($identifier);
    if ($album == false) {
        output(["message" => "Album not found"], 404);
        return;
    }

    output($album);
}

function deleteAlbum() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["albumID"]) || !is_numeric($input["albumID"])) {
        output(["message" => "Invalid or missing parameter 'albumID'"], 400);
        return;
    }

    $result = DB::DeleteAlbum($input["albumID"]);
    if (is_array($result)) {
        output(["message" => "Album deleted", "data" => $result]);
    }
    elseif ($result == -1) {
        output(["message" => "Could not delete: no such album"], 404);
    }
    else {
        output(["message" => "Could not delete album"], 400);
    }
}

function removePhoto() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["photoID"]) || !is_numeric($input["photoID"])) {
        output(["message" => "Invalid or missing parameter 'photoID'"], 400);
        return;
    }
    if (!isset($input["albumID"]) || !is_numeric($input["albumID"])) {
        output(["message" => "Invalid or missing parameter 'albumID'"], 400);
        return;
    }

    $result = DB::RemovePhotoFromAlbum($input["photoID"], $input["albumID"]);
    output(["message" => "Removed from " . $result . " album" . ($result == 1 ? "" : "s")]);
}
