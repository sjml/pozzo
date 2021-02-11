<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../util.php";

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/$", ["resetSite"], true);

$router->Route();

function resetSite() {
    DB::Reset();
    removeDir(__DIR__ . "/../../public/photos");

    header("Content-Type: application/json");
    http_response_code(200);
    $message = ["message" => "Site reset."];
    echo json_encode($message);
}
