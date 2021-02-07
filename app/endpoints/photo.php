<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../image.php";

$_REQUEST["POZZO_REQUEST"] = preg_replace(
    "/^\/photo/",
    "",
    $_REQUEST["POZZO_REQUEST"],
);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/delete", ["deletePhoto"], true);
$router->AddHandler("/copy", ["copyPhoto"], true);
$router->AddHandler("/view", ["viewPhoto"]);

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function viewPhoto() {
    $input = json_decode(file_get_contents("php://input"), true);
    $getPreview = isset($input["preview"]) && $input["preview"] == 1;

    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $photo = DB::GetPhoto($identifier, $getPreview);
    if ($photo == null) {
        output(["message" => "Photo not found"], 404);
        return;
    }

    output($photo);
}

function deletePhoto() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["photoID"]) || !is_numeric($input["photoID"])) {
        output(["message" => "Invalid or missing parameter 'photoID'"], 400);
        return;
    }

    $result = DB::DeletePhoto($input["photoID"]);

    if (!is_array($result)) {
        if ($result == -1) {
            output(["message" => "Photo not found"], 404);
            return;
        } elseif ($result == -2) {
            output(["message" => "Could not delete photo"], 500);
            return;
        }
    }

    deleteImagesWithHash($result["hash"]);

    output(["message" => "Photo deleted", "data" => $result]);
}

function copyPhoto() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["photoID"]) || !is_numeric($input["photoID"])) {
        output(["message" => "Invalid or missing parameter 'photoID'"], 400);
        return;
    }
    if (!isset($input["albumID"]) || !is_numeric($input["albumID"])) {
        output(["message" => "Invalid or missing parameter 'albumID'"], 400);
        return;
    }

    $photo = DB::GetPhoto($input["photoID"]);
    if ($photo == null) {
        output(["message" => "Photo not found"], 400);
        return;
    }
    $album = DB::FindAlbum($input["albumID"], false);
    if ($album == false) {
        output(["message" => "Album not found"], 400);
        return;
    }

    $result = DB::AddPhotoToAlbum($photo["id"], $album["id"]);
    if ($result) {
        output(["photoID" => $photo["id"], "albumID" => $album["id"]]);
    } else {
        output(["message" => "Photo already in album"], 400);
    }
}
