<?php

require_once __DIR__ . "/../image.php";

$inDir = __DIR__ . "/../../samples";
if (!is_dir($inDir)) {
    http_response_code(415);
    echo '{"error": "500 / No Input Directory"}';
    die();
}
$outDir = __DIR__ . "/../../public/img/orig";
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$contents = array_diff(scandir($inDir), [".", ".."]);
$copyCount = 0;
$outData = ["success" => [], "failure" => []];
foreach ($contents as $imgFile) {
    $photoData = importImage($inDir . "/" . $imgFile);
    if ($photoData == null) {
        array_push($outData["failure"], $inDir . "/" . $imgFile);
        continue;
    }

    processImage($photoData);
    array_push($outData["success"], $photoData);

    $copyCount += 1;
}

$message = [
    "message" =>
        "Imported " . $copyCount . " of " . count($contents) . " files.",
    "files" => $outData,
];

echo json_encode($message);
