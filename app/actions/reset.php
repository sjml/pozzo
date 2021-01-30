<?php

require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/../util.php");


DB::Reset();
removeDir(__DIR__ . "/../../public/img");

header('Content-Type: application/json');
$message = Array("message" => "Site reset.");
echo(json_encode($message));
