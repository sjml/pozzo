<?php

require_once __DIR__ . "/../db.php";

$_REQUEST["POZZO_REQUEST"] = preg_replace(
    "/^\/photo/",
    "",
    $_REQUEST["POZZO_REQUEST"],
);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/new", ["newGroup"], true);
$router->AddHandler("/edit", ["editMetadata"], true);
$router->AddHandler("/move", ["moveGroup"], true);
$router->AddHandler("/reorder", ["reorderGroup"], true);
$router->AddHandler("/merge", ["mergeGroups"], true);

$router->Route();


function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function newGroup() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["albumID"])) {
        output(["message" => "Missing 'albumID'"], 400);
        return;
    }
    if (!isset($input["fromGroup"])) {
        $input["fromGroup"] = -1;
        $input["photoIDs"] = null;
    }
    elseif (!is_numeric($input["fromGroup"])) {
        output(["message" => "Invalid parameter 'fromGroup'"], 400);
        return;
    }
    if ($input["fromGroup"] != -1) {
        if (!isset($input["photoIDs"]) || !is_array($input["photoIDs"])) {
            output(["message" => "Missing or invalid parameter 'photoIDs'"], 400);
            return;
        }
        foreach ($input["photoIDs"] as $pid) {
            if (!is_numeric($pid)) {
                output(["message" => "Non-numeric value in 'photoIDs'"], 400);
                return;
            }
        }
    }

    $result = DB::CreateGroup($input["albumID"], $input["fromGroup"], $input["photoIDs"]);
    output(["groupID" => $result]);
}

function editMetadata() {
    $input = json_decode(file_get_contents("php://input"), true);
    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $group = DB::GetGroup($identifier);
    if ($group == null) {
        output(["message" => "Group not found"], 404);
        return;
    }

    if ($input == null) {
        output(["message" => "Missing metadata parameters"], 400);
        return;
    }

    $description = array_key_exists("description", $input)
        ? $input["description"]
        : $album["description"];
    $showMap = array_key_exists("showMap", $input)
        ? $input["showMap"]
        : $album["showMap"];

    if (
        !is_string($description) ||
        !(is_numeric($showMap) || is_bool($showMap))
    ) {
        output(["message" => "Could not update metadata"], 400);
        return;
    }

    $result = DB::UpdateGroupMeta(
        $group["id"],
        $description,
        $showMap
    );

    output(["message" => "Metadata updated successfully"]);
}

function moveGroup() {
    $input = json_decode(file_get_contents("php://input"), true);
    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $group = DB::GetGroup($identifier);
    if ($group == null) {
        output(["message" => "Group not found"], 404);
        return;
    }

    if ($input == null) {
        output(["message" => "Missing parameters"], 400);
        return;
    }

    if (!array_key_exists("toAlbumID", $input) || !is_numeric($input["toAlbumID"])) {
        output(["message" => "Missing or invalid parameter 'toAlbumID'"], 400);
        return;
    }

    $album = DB::FindAlbum($input["toAlbumID"]);
    if ($album == false) {
        output(["message" => "No such album"], 404);
        return;
    }

    $result = DB::MoveGroup($identifier, $input["toAlbumID"]);
    if (!$result) {
        output(["message" => "Couldn't move group to album"], 400);
        return;
    }

    output(["message" => "Group moved successfully"]);
}

function reorderGroup() {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input["newOrdering"]) || !is_array($input["newOrdering"])) {
        output(["message" => "Invalid or missing parameter 'ordering'"], 400);
        return;
    }
    $newOrdering = $input["newOrdering"];

    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $group = DB::GetGroup($identifier);
    if ($group == null) {
        output(["message" => "Group not found"], 404);
        return;
    }

    $existingPids = [];
    foreach ($group["photos"] as $photoData) {
        array_push($existingPids, $photoData["id"]);
    }

    if (count($existingPids) != count($newOrdering)) {
        output(["message" => "Miscount of ordering data"], 400);
        return;
    }

    foreach ($newOrdering as $pid) {
        if (!in_array($pid, $existingPids)) {
            output(["message" => "Misplaced order index", "badIndex" => $pid], 400);
            return;
        }
    }

    $unique = array_unique($newOrdering);
    if (count($unique) != count($newOrdering)) {
        output(["message" => "Non-unique indices"], 400);
        return;
    }

    $result = DB::ReorderGroup($group["id"], $newOrdering);
    output(["message" => "Reordered group"]);
}

function mergeGroups() {
    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $absorbedGroup = DB::GetGroup($identifier);
    if ($absorbedGroup == null) {
        output(["message" => "Group not found"], 404);
        return;
    }

    $input = json_decode(file_get_contents("php://input"), true);
    if (!array_key_exists("into", $input) || !is_numeric($input["into"])) {
        output(["message" => "Missing or invalid parameter 'into'"], 400);
        return;
    }

    $absorbingGroup = DB::GetGroup($input["into"]);
    if ($absorbingGroup == null) {
        output(["message" => "Absorbing group not found"], 404);
        return;
    }

    if ($absorbedGroup["album_id"] != $absorbingGroup["album_id"]) {
        // guarding against accidentally merging away an album's only group?
        output(["message" => "Merging groups must be in the same album"], 400);
        return;
    }

    DB::MergeGroup($absorbingGroup["id"], $absorbedGroup["id"]);

    output(["message" => "Groups merged"]);
}
