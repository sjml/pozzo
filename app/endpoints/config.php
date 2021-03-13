<?php

require_once __DIR__ . "/../db.php";

$_REQUEST["POZZO_REQUEST"] = preg_replace(
    "/^\/config/",
    "",
    $_REQUEST["POZZO_REQUEST"],
);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/get", ["getConfig"], true);
$router->AddHandler("/set", ["setConfig"], true);

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function getConfig() {
    output(DB::GetAllConfig());
}

function setConfig() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["key"]) || !isset($input["value"]) || !isset($input["type"])) {
        output(["message" => "Missing 'key', 'value', or 'type'"], 400);
        return;
    }
    $types = ["integer", "float", "string"];
    if (array_search($input["type"], $types) === false) {
        output(["message" => "Invalid type"], 400);
        return;
    }

    $existing = DB::GetAllConfig();
    if (!isset($existing[$input["key"]])) {
        output(["message" => "No such config key"], 400);
        return;
    }

    if ($input["type"] !== $existing[$input["key"]]["type"]) {
        output(["message" => "Incorrect type"], 400);
        return;
    }

    DB::SetConfig($input["key"], $input["value"], $input["type"]);

    output(["message" => "Config value changed"]);
}
