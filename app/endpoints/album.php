<?php

require_once __DIR__ . "/../db.php";

$_REQUEST["POZZO_REQUEST"] = preg_replace(
    "/^\/album/",
    "",
    $_REQUEST["POZZO_REQUEST"],
);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/list", ["albumList"]); // give list of albums
$router->AddHandler("/new", ["newAlbum"], true);
$router->AddHandler("/view", ["viewAlbum"]);
$router->AddHandler("/remove", ["removePhoto"], true);
$router->AddHandler("/delete", ["deleteAlbum"], true);
$router->AddHandler("/reorder", ["reorderAlbum"], true);
// $router->AddHandler("/edit", ["editAlbum"], true); // change album's title or description

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function albumList() {
    $fetchPrivate = $_REQUEST["POZZO_AUTH"] > 0;
    $albums = DB::GetAlbumList($fetchPrivate);
    output($albums);
}

// create new album with given title, return index
function newAlbum() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["title"])) {
        output(["message" => "Missing 'title'"], 400);
        return;
    }
    $isPrivate = isset($input["isPrivate"]) ? $input["isPrivate"] : false;

    $result = DB::CreateAlbum($input["title"], $isPrivate);
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
    $input = json_decode(file_get_contents("php://input"), true);
    $previews = isset($input["previews"]) && $input["previews"] == 1;
    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $album = DB::FindAlbum($identifier, true, $previews);
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
    } elseif ($result == -1) {
        output(["message" => "Could not delete: no such album"], 404);
    } else {
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
    output([
        "message" =>
            "Removed from " . $result . " album" . ($result == 1 ? "" : "s"),
    ]);
}

function reorderAlbum() {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input["newOrdering"]) || !is_array($input["newOrdering"])) {
        output(["message" => "Invalid or missing parameter 'ordering'"], 400);
        return;
    }
    $newOrdering = $input["newOrdering"];

    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $album = DB::FindAlbum($identifier, true, false);
    if ($album == false) {
        output(["message" => "Album not found"], 404);
        return;
    }

    $existingPids = [];
    foreach ($album["photos"] as $photoData) {
        array_push($existingPids, $photoData["id"]);
    }

    if (count($existingPids) != count($newOrdering)) {
        output(["message" => "Miscount of ordering data"], 400);
        return;
    }

    foreach ($newOrdering as $pid) {
        if (!in_array($pid, $existingPids)) {
            output(
                ["message" => "Misplaced order index", "badIndex" => $pid],
                400,
            );
            return;
        }
    }

    $unique = array_unique($newOrdering);
    if (count($unique) != count($newOrdering)) {
        output(["message" => "Non-unique indices"], 400);
        return;
    }

    $result = DB::ReorderAlbum($album["id"], $newOrdering);
    output(["message" => "Reordered album"]);
}
